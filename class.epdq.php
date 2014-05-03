<?php

/**
 * custom class for epdq payment gateway integration in woocommerce
 * 
 * @author User
 *
 */

class WC_Nom_EPDQ extends WC_Payment_Gateway {

	public function __construct() {
		global $woocommerce;

		$this->id 			= 'epdq_checkout';
		$this->method_title = 'EPDQ Checkout';//__( '', 'woocommerce' );
		$this->icon 		= apply_filters( 'woocommerce_mijireh_checkout_icon', $woocommerce->plugin_url() . '/includes/gateways/mijireh/assets/images/credit_cards.png' );		
		$this->has_fields 	= false;
				
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		$this->title 		= $this->get_option( 'title' );
		$this->title 		= (isset($this->title) and $this->title !='') ? $this->title : 'EPDQ Checkout';
		$this->description 	= $this->get_option( 'description' );
		$this->access_key 	= $this->get_option( 'access_key' );
		$this->test_url 	= 'https://mdepayments.epdq.co.uk/ncol/test/orderstandard.asp';
		$this->live_url 	= 'https://payments.epdq.co.uk/ncol/prod/orderstandard.asp';
		$this->status 		= $this->get_option('status'); 
		$this->error_notice = $this->get_option('error_notice'); 
		$this->sha_in 		= $this->get_option('sha_in'); 
		$this->sha_out 		= $this->get_option('sha_out');		
		$this->sha_method 	= $this->get_option('sha_method');
		$this->sha_method 	= ($this->sha_method !='') ? $this->sha_method : 0;
		 
		$this->accept_url 	= $this->get_option('accept_url'); 
		$this->decline_url 	= $this->get_option('decline_url'); 
		$this->exception_url= $this->get_option('exception_url'); 
		$this->notice_url	= $this->get_option('notice_url'); 
		$this->cancel_url	= $this->get_option('cancel_url'); 
		$this->back_url		= $this->get_option('back_url'); 
		$this->home_url		= home_url(); 
		$this->cat_url		= $this->get_option('cat_url'); 
		
		$this->aavscheck = $this->get_option('aavcheck');
		$this->cvccheck = $this->get_option('cvccheck');
		
		//	templating
		
		$this->pp_format = $this->get_option('pp_format');
		$this->TITLE = $this->get_option('TITLE');
		$this->BGCOLOR = $this->get_option('BGCOLOR');
		$this->TXTCOLOR = $this->get_option('TXTCOLOR');
		$this->TBLBGCOLOR = $this->get_option('TBLBGCOLOR');
		$this->TBLTXTCOLOR = $this->get_option('TBLTXTCOLOR');
		$this->BUTTONBGCOLOR = $this->get_option('BUTTONBGCOLOR');
		$this->BUTTONTXTCOLOR = $this->get_option('BUTTONTXTCOLOR');
		$this->FONTTYPE = $this->get_option('FONTTYPE');
		$this->LOGO = $this->get_option('LOGO');
		
		
		
		// Save options
		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );		
		else
			add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
				
		// Payment listener/API hook
		add_action('woocommerce_receipt_epdq_checkout', array(&$this, 'receipt_page'));
		add_action('woocommerce_thankyou_epdq_checkout', array(&$this, 'thank_you'));
		add_action('woocommerce_api_wc_nom_epdq', array(&$this, 'check_epdq_response'));		
	}
	
	public function admin_options() {
		global $woocommerce;
		?>
		<h3><?php _e( 'Barclays EPDQ Checkout', 'woocommerce' );?></h3>
		<p><?php _e('Barclays EPDQ Payment gateway to accept payments directly into your Barclays account. This gateway will redirect the customers to the secured Barclay payment receiving page and process order there
						and then send them back to the provided links based on the status of their transection.','woocommerce')?></p>
		<p><?php _e('Sometime Barclay doesn\'t send "AAVCHECK" and "CVCCHECK" data in the transaction feedback. Though their default value is "NO" but its better not to select them in the "Dynamic e-Commerce parameters" selectbox or use them after councelling with the support desk of the company.','woocommerce')?></p>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table> <!--/.form-table-->
		
		<!-- demograph starts -->
		<br>
		<br>
		<hr>
		<h3><?php _e('This is the design of the payment processing page based on the color and font you have entered above.','woocommerce')?></h3>				
		<p><?php _e('<a href="" rel="update" class="button">UPDATE LAYOUT</a>','woocommerce');?></p>
		<hr>
		<style id="demographStyleCont">
		
			#simubody table.ncoltable2 {margin-bottom:1em;} 
			#simubody td.ncoltxtl {text-align : right; font-weight : bold;} 
			#simubody td.ncoltxtl2 {text-align : right; font-weight : bold;}
			#simubody td.ncoltxtr {text-align : left; font-weight : bold;}
			#simubody td.ncoltxtc {text-align : center; font-weight : bold;}
			#simubody td.ncollogol{text-align : right; font-weight : bold;}
			#simubody td.ncollogor{text-align : left; font-weight : bold;}
			#simubody td.ncollogoc{text-align : center; font-weight : bold;}
			#simubody td.ncoltxtmessage{text-align : left; font-weight : bold;}
			#simubody a {text-decoration: underline}
			#demographicDisplay.loading{position:relative;}
		</style>
	<div id="demographicDisplay" class="loading">
	<div class="blockUI blockOverlay" style="z-index: 1000; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background-color: rgb(255, 255, 255); opacity: 0.6; cursor: wait; background-image: url(<?php echo $woocommerce->plugin_url;?>/assets/images/ajax-loader@2x.gif); background-size: 16px; position: absolute; background-position: 50% 50%; background-repeat: no-repeat no-repeat;"></div>
		<div id="simubody">
		<br><br>
			<table border="0" width="100%">
				<tbody>
					<tr>
						<td width="15%">&nbsp;</td>
						<td width="70%">
							<div align="center">
								&nbsp;<font size="4" face=""><strong id="sumutitle"><?php bloginfo('name')?></strong></font>&nbsp;
							</div>
							<br>		
							<table class="ncoltable1" border="0" cellpadding="2" cellspacing="0" width="95%" id="ncol_ref">	    
							<tbody>
								<tr>
									<td class="ncoltxtl" colspan="1" align="right" width="50%"><small>Order reference :<!--External reference--></small></td>
									<td class="ncoltxtr" colspan="1" width="50%"><small>1326</small></td>
								</tr>
								<tr>	        
									<td class="ncoltxtl" colspan="1" align="right" width="50%">
										<small>Total charge :</small>
									</td>
								<td class="ncoltxtr" colspan="1" width="50%">
									<small>23.00 GBP
								</small>
			    			</td>
					
						</tr>			  
						<tr>
							<td class="ncoltxtl" colspan="1" align="right"><small>Beneficiary :<!--Beneficiary--></small></td>
							<td class="ncoltxtr" colspan="1"><small>Red Vatican Limited</small></td>
						</tr>					
					</tbody>
				</table>
				<br>
				<table class="ncoltable2" border="0" cellpadding="2" cellspacing="0" width="95%">
					<tbody>
						<tr>
						   	<td class="ncolh1" rowspan="1" valign="top" align="center" colspan="3">      
						    		<b><small>Please select a payment method by clicking on the logo.<!--Payment method--></small></b>    	
						 		</td>
							</tr>
						<tr>
				     		<td colspan="1" width="5%" valign="top" align="left" class="ncolline1">&nbsp;</td>
				   			<td colspan="1" width="40%" valign="top" align="left" class="ncolline1"><small><small><span class="1">  Card: SSL secured transaction</span></small></small></td>
		    				<td colspan="1" valign="top" align="center" class="ncolline1">
								<input type="image" name="VISA_brand" src="https://mdepayments.epdq.co.uk/images/VISA_choice.gif" align="middle" alt="VISA" title="VISA" class="NCOLINIM" style="margin: 3px;">
								<input type="hidden" name="paymethod" value="CreditCard">
								<input type="image" name="Eurocard_brand" src="https://mdepayments.epdq.co.uk/images/Eurocard_choice.gif" align="middle" alt="MasterCard" title="MasterCard" class="NCOLINIM" style="margin: 3px;">
							</td>
						</tr>
						<tr>
				     		<td colspan="1" width="5%" valign="top" align="left" class="ncolline2">&nbsp;</td>
				   			<td colspan="1" width="40%" valign="top" align="left" class="ncolline2"><small><small><span class="2"> </span></small></small></td>
		    				<td colspan="1" valign="top" align="center" class="ncolline2">
								<input type="image" name="Maestro_brand" src="https://mdepayments.epdq.co.uk/images/Maestro_choice.gif" align="middle" alt="Maestro" title="Maestro" class="NCOLINIM" style="margin: 3px;">
								<small><small>
									<a href="" >Can I actually pay with my Maestro card?</a>	
								</small></small>							
							</td>
						</tr>				
				  </tbody>
			</table>	
			<!-- Further information / Cancel -->
			<h2 style="display: inline; position: absolute; left: -1000px; top: -1000px; width: 0px; height: 0px; overflow: hidden;">Further information / Cancel</h2>
			<table class="ncoltable3" border="0" cellpadding="2" cellspacing="0" width="95%" id="ie_cc" style="behavior:url(#default#clientCaps)">
				<tbody><tr><td class="ncollogoc" valign="middle" align="center" width="33%">&nbsp;</td><td class="ncollogoc" valign="middle" align="center" width="33%"><img border="0" src="https://mdepayments.epdq.co.uk/images/EPDQ_BOLogoPowered.png" alt="Payment processed by Barclaycard" title="Payment processed by Barclaycard" vspace="2"><br><small><small></small></small></td><td class="ncollogoc" valign="middle" align="center" width="33%">&nbsp;</td></tr>
					<tr>
							<td class="ncollogoc">&nbsp;</td>
							<td class="ncollogoc" align="center">
									<center>
										<table border="0" cellpadding="0" cellspacing="0" width="95%">
												<tbody><tr>
													
														<td class="ncollogoc" align="center" width="50%">
														
																<form method="POST" action="" name="form3" onsubmit="" style="margin-bottom:0px;">
																
																	
																	<small><input class="ncol" id="ncol_cancel" type="submit" name="cancel" value="Cancel"></small><!--Cancel-->
																</form>
														
																	</td>
																	
																</tr>
															</tbody>
														</table>
													</center>
											</td>
											<td class="ncollogoc">&nbsp;</td>
										</tr>
									</tbody>
								</table>
							</td>
							<td width="15%">&nbsp;</td>	
						</tr>
					</tbody>
				</table>
				<br><br>
			</div>			
		</div>
		<hr>		
		<!-- demograph ends -->		
		<div class="helpMe" style="width: 300px;float: right;background: #E2EBFF;padding: 0 15px;border-left: 10px solid #6697FF;border-bottom: 1px solid #a5c2ff;">
			<p><?php _e('If you find this plugin very useful then would you mind helping me for my studies?','woocommerce');?></p>
			<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=453VDL4HEHWKQ" style="margin-top: -8px;margin-bottom: 2px;height: 20px;display: inline-block;">
				<img src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif"/>
			</a>			
					
		</div>
		<?php
		
		add_action( 'admin_enqueue_scripts', 'enqueue_colour_picker' );
		function enqueue_colour_picker(){
			wp_enqueue_script('artus-field-color-js','Field_Color.js',array('jquery', 'farbtastic'),time(),true);
			wp_enqueue_style( 'farbtastic');
		}
		wp_enqueue_script('nom_epdq_script',plugin_dir_url(__FILE__).'/script.js');
  	}
  	  	
	public function init_form_fields() {
		$this->form_fields = array( 
				'enabled' => array(
						'title' => __( 'Enable/Disable', 'woocommerce' ),
						'type' => 'checkbox',
						'label' => __( 'Enable EPDQ Checkout', 'woocommerce' ),
						'default' => 'no'
				),
				'aavcheck' => array(
						'title' => __( 'AAVCHECK.', 'woocommerce' ),
						'type' => 'checkbox',
						'label' => __( 'Set "NO" as the default value of AAVCHECK', 'woocommerce' ),
						'default' => 'no',
						'description'=>'Result of the automatic address verification. This verification is not supported by all credit card acquirers.<br>
								Possible values:<br>
								<strong>KO</strong>: The address has been sent but the acquirer has given a negative response for the address check, i.e. the address is wrong.<br>
								<strong>OK</strong>: The address has been sent and the acquirer has returned a positive response for the address check, i.e. the address is correct OR 
								The acquirer sent an authorisation code but did not return a specific response for the address check.<br>
								<strong>NO</strong>: All other cases. For instance, no address transmitted; the acquirer has replied that an address check was not possible; the acquirer declined the authorisation but did not provide a specific result for the address check.',
						'desc_tip'=>false
				),
				'cvccheck' => array(
						'title' => __( 'CVCCHECK', 'woocommerce' ),
						'type' => 'checkbox',
						'label' => __( 'Set "NO" as the default value of CVCCHECK', 'woocommerce' ),
						'default' => 'no',
						'description'=>'Result of the card verification code check. Only a few acquirers return specific CVC check results. For most acquirers, the CVC is assumed to be correct if the transaction is succesfully authorised.<br>
								Possible values:<br>								
								<strong>KO</strong>: The CVC has been sent but the acquirer has given a negative response to the CVC check, i.e. the CVC is wrong.<br>
								<strong>OK</strong>: The CVC has been sent and the acquirer has given a positive response to the CVC check, i.e. the CVC is correct OR 
								The acquirer sent an authorisation code, but did not return a specific result for the CVC check.<br>
								<strong>NO</strong>: All other cases. For instance, no CVC transmitted, the acquirer has replied that a CVC check was not possible, the acquirer declined the authorisation but did not provide a specific result for the CVC check.',
						'desc_tip'=>false
				),
				'title' => array(
						'title' => __( 'Title', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Title of the payment process. This name will be visible throughout the site and the payment page.', 'woocommerce' ),
						'default' => 'EPDQ Checkout',
						'desc_tip'      => true
				),
				'description' => array(
						'title' => __( 'Description', 'woocommerce' ),
						'type' => 'textarea',
						'description' => __( 'Description of the payment process. This description will be visible throuhout the site and the payment page.', 'woocommerce' ),
						'default' => 'Use the payment processor of barclay bank and checkout with your debit/credit card.',
						'desc_tip'      => true
				),
				'access_key' => array(
						'title' => __( 'PSPID', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'The PSPID for your barclays account. This is the id which you use to login the admin panel of the barclays bank.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => true
				),
				'status' => array(
						'title' => __( 'Store Status', 'woocommerce' ),
						'type' => 'select',
						'options'=> array('test'=>'Test Environment','live'=>'Live Store'),
						'description' => __( 'The status of your store tells that are you actually ready to run your shop or its still a test environment. If the test is selected then no payments will be processed. For details please refer to the user guide provided by the Barclays EPDQ servise.', 'woocommerce' ),						
						'default' => '',
						'desc_tip'      => true,
				),
				'sha_in' => array(
						'title' => __( 'SHA-IN Passphrase', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'The SHA-IN signature will encode the parameter passed to the payment processor via the hidden fields to ensure better security.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => true
				),
				'sha_out' => array(
						'title' => __( 'SHA-OUT Passphrase', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'The SHA-OUT signature will encode the parameter passed to the redirection url from the payment processor to ensure better security.', 'woocommerce' ),
						'default' => 0,
						'desc_tip'      => true
				),
				'sha_method' => array(
						'title' => __( 'SHA encription method', 'woocommerce' ),
						'type' => 'select',
						'options'=> array(0=>'SHA-1',1=>'SHA-256',2=>'SHA-512'),
						'description' => __( 'Sha encryption method - this needs to be similar waht you have set in the epdq backoffice.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => true,
				),
				'error_notice' => array(
						'title' => __( 'Error Notice', 'woocommerce' ),
						'type' => 'textarea',
						'description' => __( 'In case if there something went wrong while checking out what message will be displayed to the customer.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => true
				),				
				'back_url' => array(
						'title' => __( 'Back Url', 'woocommerce' ),
						'type' => 'select',
						'options'=> $this->get_pages('Select back url'),
						'description' => __( 'The url where the customer will be redirected if they clcik on theback button. Use the pages list to redirect the customer to that page and show message regarding this. If left blank then the previous cart page will be shown.', 'woocommerce' ),						
						'default' => '',
						'desc_tip'      => true,
				),
				'cat_url' => array(
						'title' => __( 'Catalogue Url', 'woocommerce' ),
						'type' => 'select',
						'options'=> $this->get_pages('Select catalogue url'),
						'description' => __( 'URL of your catalogue. When the transaction has been processed, your customer is requested to return to this URL via a button.. If blank base shop link of the woocommerce installation will be provided.', 'woocommerce' ),						
						'default' => '',
						'desc_tip'      => true,
				),
				'pp_format' => array(
						'title' => __( 'Payment Page', 'woocommerce' ),
						'type' => 'checkbox',
						'label' => __( 'Enable payment page formatting', 'woocommerce' ),
						'default' => 'no'
				),
				'TITLE'=> array(
						'title' => __( 'Payment Page Title', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Title of the payment page. This name will be visible in the title bar of the payment page.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => false
				),
				'BGCOLOR'=> array(
						'title' => __( 'Background Color', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Background color of the payment page.', 'woocommerce' ),
						'default' => '#000',
						'class'=>'popup-colorpicker',
						'append'=>'<div id="woocommerce_epdq_checkout_BGCOLORpicker" class="color-picker"></div>',
						'desc_tip'      => false
				),
				'TXTCOLOR'=> array(
						'title' => __( 'Text Color', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Text color of the payment page.', 'woocommerce' ),
						'default' => '#000',
						'class'=>'popup-colorpicker',
						'append'=>'<div id="woocommerce_epdq_checkout_TXTCOLORpicker" class="color-picker"></div>',
						'desc_tip'      => false
				),
				'TBLBGCOLOR'=> array(
						'title' => __( 'Table Background Color', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Table background color of the payment page.', 'woocommerce' ),
						'default' => '#000',
						'class'=>'popup-colorpicker',
						'append'=>'<div id="woocommerce_epdq_checkout_TBLBGCOLORpicker" class="color-picker"></div>',
						'desc_tip'      => false
				),
				'TBLTXTCOLOR'=> array(
						'title' => __( 'Table Text Color', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Table text color of the payment page.', 'woocommerce' ),
						'default' => '#000',
						'class'=>'popup-colorpicker',
						'append'=>'<div id="woocommerce_epdq_checkout_TBLTXTCOLORpicker" class="color-picker"></div>',
						'desc_tip'      => false
				),
				'BUTTONBGCOLOR'=> array(
						'title' => __( 'Button Background Color', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Button background color of the payment page.', 'woocommerce' ),
						'default' => '#000',
						'class'=>'popup-colorpicker',
						'append'=>'<div id="woocommerce_epdq_checkout_BUTTONBGCOLORpicker" class="color-picker"></div>',
						'desc_tip'      => false
				),
				'BUTTONTXTCOLOR'=> array(
						'title' => __( 'Button Text Color', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Button text color of the payment page.', 'woocommerce' ),
						'default' => '#000',
						'class'=>'popup-colorpicker',
						'append'=>'<div id="woocommerce_epdq_checkout_BUTTONTXTCOLORpicker" class="color-picker"></div>',
						'desc_tip'      => false
				),
				'FONTTYPE'=> array(
						'title' => __( 'Font Type', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Font type of the payment page.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => false
				),
				'LOGO'=> array(
						'title' => __( 'Logo', 'woocommerce' ),
						'type' => 'text',
						'description' => __( 'Logo in the payment page. This logo url must be stored in a ssl enabled location or else it won\'t be shown.', 'woocommerce' ),
						'default' => '',
						'desc_tip'      => false
				)
				
		);
		
	}
	function process_payment($order_id){
        global $woocommerce;
    	$order = new WC_Order( $order_id );
        return array('result' => 'success', 'redirect' => add_query_arg(array('key' => $order->order_key), $order->get_checkout_payment_url( $on_checkout = true )));
    }
	
	function receipt_page($order_id){
		global $woocommerce;
		$order = new WC_Order( $order_id );
		    
                $order_received_url = add_query_arg( 'wc-api', 'WC_Nom_EPDQ', $order->get_checkout_order_received_url() );
        $fields = array(        
        		'PSPID'=>$this->access_key,
        		'ORDERID'=>$order_id,
        		'AMOUNT'=>$order->order_total*100,
        		'CURRENCY'=>get_woocommerce_currency(),
        		'LANGUAGE'=>get_bloginfo('language'),
        		'CN'=>$order->billing_first_name . ' ' . $order->billing_last_name,
        		'EMAIL'=>$order->billing_email,
        		'OWNERZIP'=>$order->billing_postcode,
        		'OWNERADDRESS'=>$order->billing_address_1,
        		'OWNERADDRESS2'=>$order->billing_address_2,
        		'OWNERCTY'=>$woocommerce->countries->countries[$order->billing_country],
        		'OWNERTOWN'=>$order->billing_city, 
        		'OWNERTELNO'=>$order->billing_phone,
        		
        		'ACCEPTURL'=>$order_received_url,
                        'DECLINEURL'=>$order_received_url,
                        'EXCEPTIONURL'=>$order_received_url,
                        'CANCELURL'=>$order_received_url,        		

        		'BACKURL'=>get_permalink($this->back_url),
        		'HOMEURL'=>get_permalink($this->home_url),
        		'CATALOGURL'=>get_permalink($this->cat_url),
        );		
        		
        if( $this->pp_format == 'yes' ){
        	
        	$fields['TITLE'] 			= $this->TITLE;
        	$fields['BGCOLOR']			= $this->BGCOLOR;
        	$fields['TXTCOLOR']			= $this->TXTCOLOR;
        	$fields['TBLBGCOLOR']		= $this->TBLBGCOLOR;
        	$fields['TBLTXTCOLOR']		= $this->TBLTXTCOLOR;
			$fields['BUTTONBGCOLOR']	= $this->BUTTONBGCOLOR;
        	$fields['BUTTONTXTCOLOR']	= $this->BUTTONTXTCOLOR;
        	$fields['FONTTYPE']			= $this->FONTTYPE;
        	$fields['LOGO'] 			= $this->LOGO;
        	
        }	
        
        
        $shasign ='';
        $shasign_arg = array();
        ksort($fields);
        foreach($fields as $key => $value){
        	if($value=='') continue;
        	$shasign_arg[] =  $key .'='. $value;
        }
        
        if( $this->sha_method == 0 )
        	$shasign = sha1(implode($this->sha_in, $shasign_arg).$this->sha_in);
        elseif ( $this->sha_method == 1 )
        	$shasign = hash('sha256',implode($this->sha_in, $shasign_arg).$this->sha_in);
        elseif ( $this->sha_method == 2 )
        	$shasign = hash('sha512',implode($this->sha_in, $shasign_arg).$this->sha_in);
        else{}
        
        
        $epdq_args = array();
        foreach($fields as $key => $value){
        	if($value=='') continue;
			$epdq_args[] = "<input type='hidden' name='$key' value='$value'/>";
        }
        
        if( isset($this->status) and ($this->status=='test' or $this->status =='live') ):
        	if($this->status=='test')	$url = $this->test_url;
        	if($this->status=='live')	$url = $this->live_url;
        	
			echo '<p>'.__('Thank you for your order, please click the button below to pay securely', 'woocommerce').'</p>';
			echo '<form action="'.$url.'" method="post" id="epdq_payment_form">';
			echo implode('', $epdq_args);			
			echo '<input type="hidden" name="SHASIGN" value="'.$shasign.'"/>';
			echo '<input type="submit" class="button alt" id="submit_epdq_payment_form" value="'.__('Pay securely', 'woocommerce').'" />';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'woocommerce').'</a></form>';
        else:
        	echo '<p class="error">'.$this->error_notice.'</p>';
		endif;
    }
    
    
    function check_epdq_response(){
    	@ob_clean();    	
    	header( 'HTTP/1.1 200 OK' );
    	
    		$x = array(
    			'ORDERID'	=>	isset($_REQUEST['orderID']) ? $_REQUEST['orderID'] : '',
    			'CURRENCY'	=>	isset($_REQUEST['currency']) ? $_REQUEST['currency'] : '',
    			'AMOUNT'	=>	isset($_REQUEST['amount']) ? $_REQUEST['amount'] : '',
    			'PM'		=>	isset($_REQUEST['PM']) ? $_REQUEST['PM'] : '',    			
    			'STATUS'	=>	isset($_REQUEST['STATUS']) ? $_REQUEST['STATUS'] : '',
    			'CARDNO'	=>	isset($_REQUEST['CARDNO']) ? $_REQUEST['CARDNO'] : '',
    			'ED'		=>	isset($_REQUEST['ED']) ? $_REQUEST['ED'] : '',
    			'CN'		=>	isset($_REQUEST['CN']) ? $_REQUEST['CN'] : '',
    			'TRXDATE'	=>	isset($_REQUEST['TRXDATE']) ? $_REQUEST['TRXDATE'] : '',
    			'PAYID'		=>	isset($_REQUEST['PAYID']) ? $_REQUEST['PAYID'] : '',
    			'NCERROR'	=>	isset($_REQUEST['NCERROR']) ? $_REQUEST['NCERROR'] : '',
    			'BRAND'		=>	isset($_REQUEST['BRAND']) ? $_REQUEST['BRAND'] : '',
    			'IP'		=>	isset($_REQUEST['IP']) ? $_REQUEST['IP'] : '',
    			'AAVADDRESS'    =>	isset($_REQUEST['AAVADDRESS']) ? $_REQUEST['AAVADDRESS'] : '',
    			'AAVCHECK'	=>	isset($_REQUEST['AAVCheck']) ? $_REQUEST['AAVCheck'] : ($this->aavscheck == 'yes')? 'NO' : '',
    			'AAVZIP'	=>	isset($_REQUEST['AAVZIP']) ? $_REQUEST['AAVZIP'] : '',
    			'AAVMAIL'	=>	isset($_REQUEST['AAVMAIL']) ? $_REQUEST['AAVMAIL'] : '',
    			'AAVNAME'	=>	isset($_REQUEST['AAVNAME']) ? $_REQUEST['AAVNAME'] : '',
    			'AAVPHONE'	=>	isset($_REQUEST['AAVPHONE']) ? $_REQUEST['AAVPHONE'] : '',
    			'ACCEPTANCE'    =>	isset($_REQUEST['ACCEPTANCE']) ? $_REQUEST['ACCEPTANCE'] : '',
    			'BIN'		=>	isset($_REQUEST['BIN']) ? $_REQUEST['BIN'] : '',
    			'CCCTY'		=>	isset($_REQUEST['CCCTY']) ? $_REQUEST['CCCTY'] : '',
    			'COMPLUS'	=>	isset($_REQUEST['COMPLUS']) ? $_REQUEST['COMPLUS'] : '',
    			'CVCCHECK'	=>	isset($_REQUEST['CVCCheck']) ? $_REQUEST['CVCCheck'] : ($this->cvccheck == 'yes')? 'NO' : '',
    			'ECI'		=>	isset($_REQUEST['ECI']) ? $_REQUEST['ECI'] : '',
    			'FXAMOUNT'	=>	isset($_REQUEST['FXAMOUNT']) ? $_REQUEST['FXAMOUNT'] : '',
    			'FXCURRENCY'    =>	isset($_REQUEST['FXCURRENCY']) ? $_REQUEST['FXCURRENCY'] : '',
    			'IPCTY'		=>	isset($_REQUEST['IPCTY']) ? $_REQUEST['IPCTY'] : '',
    			'SUBBRAND'	=>	isset($_REQUEST['SUBBRAND']) ? $_REQUEST['SUBBRAND'] : '',
    			'VC'		=>	isset($_REQUEST['VC']) ? $_REQUEST['VC'] : '',
    		);
    		    		
    		$SHASIGN = isset($_REQUEST['SHASIGN']) ? $_REQUEST['SHASIGN'] : '';
    		ksort($x);    		
    		$xy = '';
    		
    		foreach($x as $k=>$v){
    			if( $v=='' )	continue;
    			$xy.=strtoupper($k).'='.$v.$this->sha_out;
    		}    		
    		
    		if( $this->sha_method == 0 )
    			$shasignxy = sha1($xy);
    		elseif ( $this->sha_method == 1 )
    			$shasignxy = hash('sha256',$xy);
    		elseif ( $this->sha_method == 2 )
    			$shasignxy = hash('sha512',$xy);
    		else{}
    		
    		
    		if( strtolower($shasignxy) == strtolower($SHASIGN) ){ 
    			
    			$this->transaction_successfull( $x );    			
    		}
    		else{
    			wp_die('Transaction verification error!');
    		}    		
    	
    }
    
    
    function transaction_successfull($args){
    	
    	global $woocommerce;
    	
    	extract($args);
    	$order = new WC_Order( $ORDERID );

    	$accepted = array(4,5,9,41,51,91);
    	$status = $STATUS;
    	
    	$dienote = '<p>Transaction result is uncertain.<p>';
    	$dienote .='<p>Status Code: '.$STATUS.' - '.$this->get_epdq_status_code($status).'';
    	$dienote .='<br>Error Code: '.$NCERROR.'</p>';
    	$died = '';    	
    	$died .= $dienote;
    	$died .='<p>Your order is cancelled and your cart is emptied.';
    	$died .='<br>Go to your <a href="'.get_permalink(get_option( 'woocommerce_myaccount_page_id' )).'">account</a> to process your order again or ';
    	$died .='go to <a href="'.home_url().'">homepage</a></p>';
    	
    	if(in_array($STATUS, $accepted)){
    		
    		if( !empty($args["ORDERID"]) )
	    		$note 	= 'Order ID: '.$ORDERID.'.<br>';			//	order id 
    		if( !empty($args["AMOUNT"]) )	
    			$note .= 'Amount: '.$AMOUNT.'.<br>';				//	amount    		
    		if( !empty($args["CURRENCY"]) )
    			$note .= 'Order currency: '.$CURRENCY.'.<br>';		//	order currency
    		if( !empty($args["PM"]) )
		    	$note .= 'Payment Method: '.$PM.'.<br>';			//	payment method
    		if( !empty($args["ACCEPTANCE"]) )
		    	$note .= 'Acceptance code returned by acquirer: '.$ACCEPTANCE.'.<br>';	//	acceptance
    		if( !empty($args["STATUS"]) )
		    	$note .= 'Transaction status : '.$STATUS.'.<br>';			//	status code
    		if( !empty($args["CARDNO"]) )
    			$note .= 'Masked card number : '.$CARDNO.'.<br>';		//	catd no
    		if( !empty($args["PAYID"]) )
		    	$note .= 'Payment reference in EPDQ system: '.$PAYID.'.<br>';		//	pay id
    		if( !empty($args["NCERROR"]) )
    			$note .= 'Error Code: '.$NCERROR.'.<br>';				//	ncerror
    		if( !empty($args["BRAND"]) )
    			$note .= 'Card brand (EPDQ system derives this from the card number) : '.$BRAND.'.<br>';	//	brand
    		if( !empty($args["ED"]) )
    			$note .= 'Payer\'s card expiry date : '.$ED.'.<br>';	//	expiry date
    		if( !empty($args["TRXDATE"]) )
    			$note .= 'Transaction Date: '.$TRXDATE.'.<br>';		//	date
    		if( !empty($args["CN"]) )
    			$note .= 'Cardholder/customer name: '.$CN.'.<br>';				//	payer's name
    		if( !empty($args["IP"]) )
		    	$note .= 'Customer\'s IP: '.$IP.'.<br>';				//	payer's ip
    		
    		
    		
    		
    		if( !empty($args["AAVADDRESS"]) )
    			$note .= 'AAV result for the address: '.$AAVADDRESS.'.<br>';			//	aav address
    		if( !empty($args["AAVCHECK"]) )
    			$note .= 'Result of the automatic address verification: '.$AAVCHECK.'.<br>';				//	aav check
    		if( !empty($args["AAVZIP"]) )
    			$note .= 'AAV result for the zip code: '.$AAVZIP.'.<br>';				// aav zip
    		if( !empty($args["BIN"]) )
    			$note .= 'First 6 digits of credit card number: '.$BIN.'.<br>';					// bin
    		if( !empty($args["CCCTY"]) )
    			$note .= 'Country where the card was issued: '.$CCCTY.'.<br>';
    		if( !empty($args["COMPLUS"]) )
    			$note .= 'Custom value passed: '.$COMPLUS.'.<br>';
    		
    		if( !empty($args["CVCCHECK"]) )
    			$note .= 'Result of the card verification code check: '.$CVCCHECK.'.<br>';
    		if( !empty($args["ECI"]) )
    			$note .= 'Electronic Commerce Indicator: '.$ECI.'.<br>';
    		if( !empty($args["FXAMOUNT"]) )
    			$note .= 'FXAMOUNT: '.$FXAMOUNT.'.<br>';
    		if( !empty($args["FXCURRENCY"]) )
    			$note .= 'FXCURRENCY: '.$FXCURRENCY.'.<br>';
    		if( !empty($args["IPCTY"]) )
    			$note .= 'Originating country of the IP address: '.$IPCTY.'.<br>';
    		if( !empty($args["SUBBRAND"]) )
    			$note .= 'SUBBRAND: '.$SUBBRAND.'.<br>';
    		if( !empty($args["VC"]) )
    			$note .= 'Virtual Card type: '.$SUBBRAND.'.<br>';    		
	    	
    		
    		
	    	$woocommerce->cart->empty_cart();
	    	if( in_array($STATUS,array(4,5,9))  ){
	    		$noteT  = 'Barclay ePDQ transaction is confirmed.<br>';
	    		$note .= $noteT;
	    		$order->add_order_note($note);
	    		$order->payment_complete();
	    	}
	    	if( in_array($STATUS,array(41,51,91))  ){
	    		$noteT  = 'Barclay ePDQ transaction is awaiting for confirmation.<br>';
	    		$note .= $noteT;
	    		$order->update_status('on-hold',$note);
	    	}
	    	
	    	//wp_die($note);
	    	header('Location:'.add_query_arg('key', $order->order_key, $this->accept_url));
    	}
    	elseif($STATUS==2 or $STATUS== 93){
    		$dienote .='<br>Order is failed.';
    		$order->update_status('failed',$dienote);
    		$woocommerce->cart->empty_cart();
    		header('Location:'.add_query_arg(array('key' => $order->order_key,'statusCode'=> $STATUS,'ncerror'=>$NCERROR), $this->decline_url));
    	}
    	elseif($STATUS== 52 or $STATUS== 92){
    		$dienote .='<br>Order is failed.';
    		$order->update_status('failed',$dienote);
    		$woocommerce->cart->empty_cart();
    		header('Location:'.add_query_arg(array('key' => $order->order_key,'statusCode'=> $STATUS,'ncerror'=>$NCERROR), $this->exception_url));    	
    	}
    	elseif( $STATUS==1 ){
    		$dienote .='<br>Order is cancelled.';
    		$order->update_status('cancelled',$dienote);
    		$woocommerce->cart->empty_cart();
    		header('Location:'.add_query_arg(array('key' => $order->order_key,'statusCode'=> $STATUS,'ncerror'=>$NCERROR), $this->cancel_url));
    	}
    	else{
    		$dienote .='<br>Order is failed.';
    		$order->update_status('failed',$dienote);
    		$woocommerce->cart->empty_cart();
    		header('Location:'.add_query_arg(array('key' => $order->order_key,'statusCode'=> $STATUS,'ncerror'=>$NCERROR), isset($this->notice_url)? $this->notice_url : $this->cancel_url));
    	}
    	
		//wp_die($died);
		
    }
    
    function payment_fields() {
		echo '<img src="'.plugin_dir_url(__FILE__).'epdq.gif"/>';
		if ( $description = $this->get_description() )
			echo wpautop( wptexturize( $description ) );		
	}
	
	function thank_you(){
		if( isset($_REQUEST['statusCode']) and isset($_REQUEST['ncerror']) ){
			echo '<div class="errorDetails">'; 
			echo '<h4 class="errorTitle">Error details</h4>';			
			echo '<p><strong>Status code</strong>:&nbsp;<em>'.$_REQUEST['statusCode'].'</em> - '. $this->get_epdq_status_code($_REQUEST['statusCode']).'</p>';
			echo '<p><strong>NCERROR</strong>:&nbsp;<em>'.$_REQUEST['ncerror'].'</em> - '.$this->get_epdq_ncerror($_REQUEST['ncerror']).'.</p>';			
			echo '</div>';
		}
	}
	
	/**
	 * some helper methods
	 */
	function get_pages($title = false, $indent = true) {
		$wp_pages = get_pages('sort_column=menu_order');
		$page_list = array();
		if ($title) $page_list[] = $title;
		foreach ($wp_pages as $page) {
			$prefix = '';
			// show indented child pages?
			if ($indent) {
				$has_parent = $page->post_parent;
				while($has_parent) {
					$prefix .=  ' --- ';
					$next_page = get_page($has_parent);
					$has_parent = $next_page->post_parent;
				}
			}
			// add to page list array array
			$page_list[$page->ID] = $prefix . $page->post_title;
		}
		return $page_list;
	}
	
	function get_epdq_status_code($code){
		if( $code == '' )
			return;
		$codes =  array(		
			0=>'Incomplete or invalid',
			1=>'Cancelled by client',
			2=>'Authorisation refused',
			4=>'Order stored',
			40=>'Stored waiting external result',
			41=>'Waiting client payment',
			5=>'Authorised',
			51=>'Authorisation waiting',
			52=>'Authorisation not known',
			55=>'Standby',
			56=>'OK with scheduled payments',
			57=>'Not OK with scheduled payments',
			59=>'Author. to get manually',
			6=>'Authorised and canceled',
			61=>'Author. deletion waiting',
			62=>'Author. deletion uncertain',
			63=>'Author. deletion refused',
			64=>'Authorised and cancelled',
			7=>'Payment deleted',
			71=>'Payment deletion pending',
			72=>'Payment deletion uncertain',
			73=>'Payment deletion refused',
			74=>'Payment deleted (not accepted)',
			75=>'Deletion processed by merchant',
			8=>'Refund',
			81=>'Refund pending',
			82=>'Refund uncertain',
			83=>'Refund refused',
			84=>'Payment declined by the acquirer (will be debited)',
			85=>'Refund processed by merchant',
			9=>'Payment requested',
			91=>'Payment processing',
			92=>'Payment uncertain',
			93=>'Payment refused',
			94=>'Refund declined by the acquirer',
			95=>'Payment processed by merchant',
			96=>'Refund reversed',
			97=>'Being processed - intermediate technical status',
			98=>'Being processed - intermediate technical status',
			99=>'Being processed - intermediate technical status'
		);
		if (isset($codes[$code]))
			return $codes[$code];
		else
			return 'Unknown';
	}
	
	function get_epdq_ncerror($code) {
		if ($code == '')
			return;
		
		$code = (int) $code;	
		$ncerorr_list=array(
				
			20001001=>'Authorisation failed. Please retry.',
			20001002=>'Authorisation failed. Please retry.',
			20001003=>'Authorisation failed. Please retry.',
			20001004=>'Authorisation failed. Please retry.',
			20001005=>'Authorisation failed. Please retry.',
			20001006=>'Authorisation failed. Please retry.',
			20001007=>'Authorisation failed. Please retry.',
			20001008=>'Authorisation failed. Please retry.',
			20001009=>'Authorisation failed. Please retry.',
			20001010=>'Authorisation failed. Please retry.',
			30001999=>'Our payment system is currently under maintenance, please try later.',
			50001005=>'Expiry date error.',
			50001007=>'Requested operation code not permitted.',
			50001008=>'Invalid time limit value.',
			50001010=>'Invalid input date format.',
			50001013=>'Unable to parse socket input stream.',
			50001014=>'Error in parsing stream content.',
			50001015=>'Currency error.',
			50001016=>'Transaction still posted at end of wait.',
			50001017=>'Sync value not compatible with delay value.',
			50001019=>'Duplicate of a pre-existing transaction.',
			50001020=>'Acceptance code required for transaction.',
			50001024=>'Maintenance acquirer differs from original transaction acquirer.',
			50001025=>'Maintenance merchant differs from original transaction merchant.',
			50001028=>'Maintenance operation not appropriate for original transaction.',
			50001031=>'Host application unknown for the transaction.',
			50001032=>'Unable to perform requested operation with requested currency.',
			50001033=>'Maintenance card number differs from original transaction card number.',
			50001034=>'Operation code not permitted.',
			50001035=>'Exception occurred in socket input stream processing.',
			50001036=>'Card length does not correspond to an acceptable value for the brand.',
			50001036=>'Card length does not correspond to an acceptable value for the brand.',
			50001068=>'A technical problem has occurred. Please contact the helpdesk.',
			50001069=>'Invalid check for CardID and Brand.',
			50001070=>'A technical problem has occurred. Please contact the helpdesk.',
			50001116=>'Unknown origin IP.',
			50001117=>'No origin IP detected.',
			50001118=>'Merchant configuration problem. Please contact support.',
			10001001=>'Communication failure.',
			10001002=>'Communication failure.',
			10001003=>'Communication failure.',
			10001004=>'Communication failure.',
			10001005=>'Communication failure.',
			10001016=>'Waiting for acquirer feedback.',
			10001018=>'3XCB pending transaction awaiting for Final status.',
			20001001=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001002=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the status of the transaction within one working day. Please check the status later.',
			20001003=>'We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction. Please check the status later.',
			20001004=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001005=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001006=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001007=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001008=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001009=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001010=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001101=>'A technical problem has occurred. Please contact the helpdesk.',
			20001104=>'We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction. Please check the status later.',
			20001105=>'We have received an unknown status for the transaction. We shall contact your acquirer and update the transaction status within one working day. Please check the status later.',
			20001111=>'A technical problem has occurred. Please contact the helpdesk.',
			20001998=>'We received an unknown status for the transaction. We will contact your acquirer and update the status of the transaction. Please check the status later.',
			20002001=>'Bank response origin cannot be checked.',
			20002002=>'Beneficiary account number has been modified during processing.',
			20002003=>'Amount has been modified during processing.',
			20002004=>'Currency has been modified during processing.',
			20002005=>'No feedback detected from the bank server.',
			30001001=>'Payment refused by the financial institution.',
			30001002=>'Duplicate request.',
			30001010=>'A technical problem has occurred. Please contact the helpdesk.',
			30001011=>'A technical problem has occurred. Please contact the helpdesk.',
			30001012=>'Card blacklisted - Contact acquirer.',
			30001015=>'There has been a connection error to the receiving bank. Please try later or choose another payment method.',
			30001016=>'Transmission failure and/or Database error. The transaction could not be properly initialised in the send process (db access failures, etc.).',
			30001051=>'A technical problem has occurred. Please contact the helpdesk.',
			30001054=>'A technical problem has occurred. Please contact the helpdesk.',
			30001056=>'Your merchant\'s acquirer is temporarily unavailable, please try later or choose another payment method.',
			30001057=>'There has been a connection error to the receiving bank. Please try later or choose another payment method.',
			30001058=>'There has been a connection error to the receiving bank. Please try later or choose another payment method.',
			30001060=>'Acquirer has i`ndicated a failure during payment processing.',
			30001070=>'RATEPAY Invalid Response Type (Failure).',
			30001071=>'RATEPAY Missing Mandatory status code field (failure).',
			30001072=>'RATEPAY Missing Mandatory Result code field (failure).',
			30001073=>'RATEPAY Response parsing Failed.',
			30001090=>'CVC check required by front end and returned invalid by acquirer.',
			30001091=>'Postcode check required by front end and returned invalid by acquirer.',
			30001092=>'Address check required by frontend and returned as invalid by acquirer.',
			30001100=>'Unauthorised customer country.',
			30001101=>'IP country differs from card country.',
			30001102=>'Number of different countries too high.',
			30001103=>'unauthorised card country.',
			30001104=>'unauthorised IP address country.',
			30001105=>'Anonymous proxy.',
			30001110=>'If the problem persists, please contact Support or go to paysafecard\'s card balance page (https://customer.cc.at.paysafecard.com/psccustomer/GetWelcomePanelServlet?language=en), to see when the amount reserved on your card will be available again.',
			30001120=>'IP address on merchant\'s blacklist.',
			30001130=>'BIN on merchant\'s blacklist.',
			30001131=>'Wrong BIN for 3xCB.',
			30001140=>'Card on merchant\'s blacklist.',
			30001141=>'E-mail blacklisted.',
			30001142=>'Passenger name blacklisted.',
			30001143=>'Cardholder name blacklisted.',
			30001144=>'Passenger name different from owner name.',
			30001145=>'Time to departure too short.',
			30001149=>'Card Configured in Card Supplier Limit for another relation (CSL).',
			30001150=>'Card not configured in the system for this customer (CSL).',
			30001151=>'REF1 not allowed for this relationship (Contract number).',
			30001152=>'Card/Supplier Amount limit reached (CSL).',
			30001153=>'Card not permitted for this supplier (Date out of contractual limits).',
			30001154=>'You have reached the permitted usage limit.',
			30001155=>'You have reached the permitted usage limit.',
			30001156=>'You have reached the permitted usage limit.',
			30001157=>'Unauthorised IP country for itinerary.',
			30001158=>'e-mail usage limit reached.',
			30001159=>'Unauthorised card country/IP country combination.',
			30001160=>'Postcode in high-risk group.',
			30001161=>'generic blacklist match.',
			30001162=>'Invoicing Address is a PO Box.',
			30001163=>'Airport in high-risk group.',
			30001164=>'Shipping Method in high-risk group.',
			30001165=>'Shipping Method Details in high-risk group.',
			30001166=>'Product Category in high-risk group.',
			30001167=>'Subbrand in high-risk group.',
			30001168=>'Issuer Number in high-risk group.',
			30001169=>'Time to delivery too short.',
			30001180=>'maximum scoring reached.',
			30001997=>'Authorisation cancelled by simulator.',
			30001998=>'A technical problem has occurred. Please try again.',
			30001999=>'There has been a connection error with the receiving bank. Please try later or choose another payment method.',
			30002001=>'Payment refused by the financial institution.',
			30002001=>'Payment refused by the financial institution.',
			30021001=>'Please call the acquirer support call number.',
			30022001=>'Payment must be approved by the acquirer prior to execution.',
			30031001=>'Invalid merchant number.',
			30041001=>'Retain card.',
			30051001=>'Authorisation declined.',
			30051002=>'Voor vragen over uw afwijzing kunt u contact opnemen met de @STARTURL@http://www.afterpay.nl/consument-contact@TXTURL@Klantenservice van AfterPay@ENDURL@.',
			30051009=>'It is possible that you may not have completed all the required information (correctly) during the order process.',
			30051010=>'because your age is under 18. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=2@TXTURL@website of AfterPay@ENDURL@.',
			30051011=>'because your address could not be validated. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=3@TXTURL@website of AfterPay@ENDURL@.',
			30051012=>'because your emailadres is invalid. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=4@TXTURL@website van AfterPay@ENDURL@.',
			30051013=>'because the order amount extends the limit for first time AfterPay users. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=5@TXTURL@website of AfterPay@ENDURL@.',
			30051014=>'because there are currently too many outstanding payments at AfterPay. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=6@TXTURL@website of AfterPay@ENDURL@.',
			30051015=>'because your chamber of commerce number could not be validated. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=7@TXTURL@website of AfterPay@ENDURL@.',
			30051016=>'because the order amount is too low. For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=8@TXTURL@website of AfterPay@ENDURL@.',
			30051017=>'For more information please visit @STARTURL@http://www.afterpay.nl/rej.php?LAN=$LANGUAGE$&TEMPLATE=1@TXTURL@website of AfterPay@ENDURL@.',
			30071001=>'Retain card - special conditions.',
			30121001=>'Invalid transaction.',
			30131001=>'Invalid amount.',
			30131002=>'You have reached the permitted limit.',
			30141001=>'Invalid card number.',
			30151001=>'Unknown acquiring institution.',
			30171001=>'Payment method cancelled by the customer.',
			30171002=>'The maximum time allowed has elapsed.',
			30191001=>'Please try again later.',
			30201001=>'A technical problem has occurred. Please contact the helpdesk.',
			30301001=>'Invalid format.',
			30311001=>'Unknown acquirer ID.',
			30331001=>'Card expired.',
			30341001=>'Suspicion of fraud.',
			30341001=>'Suspicion of fraud.',
			30341002=>'Suspicion of fraud (3rdMan).',
			30341003=>'Suspicion of fraud (Perseuss).',
			30341004=>'Suspicion of fraud (ETHOCA).',
			30341005=>'Suspicion of fraud (Expert).',
			30381001=>'A technical problem has occurred. Please contact the helpdesk.',
			30401001=>'Invalid function.',
			30411001=>'Lost card.',
			30431001=>'Stolen card. Pick up.',
			30511001=>'Insufficient funds.',
			30521001=>'No Authorisation. Please contact your card issuer.',
			30541001=>'Card expired.',
			30551001=>'Invalid PIN.',
			30561001=>'Card not in authoriser\'s database.',
			30571001=>'Transaction not permitted on card.',
			30581001=>'Transaction not permitted on this terminal.',
			30591001=>'Suspicion of fraud.',
			30601001=>'The merchant should contact the acquirer.',
			30611001=>'Amount exceeds card limit.',
			30621001=>'Restricted card.',
			30631001=>'Security policy not respected.',
			30641001=>'Amount changed from ref. transaction.',
			30681001=>'The maximum allowed time has elapsed.',
			30751001=>'Incorrect PIN entered too many times.',
			30761001=>'Already disputed by cardholder.',
			30771001=>'PIN entry required.',
			30811001=>'Message flow error.',
			30821001=>'Authorisation centre unavailable.',
			30831001=>'Authorisation centre unavailable.',
			30901001=>'Temporary system shutdown.',
			30911001=>'Acquirer unavailable.',
			30921001=>'Invalid card type for acquirer.',
			30941001=>'Duplicate transaction.',
			30961001=>'Processing temporarily not possible.',
			30971001=>'A technical problem has occurred. Please contact the helpdesk.',
			30981001=>'A technical problem has occurred. Please contact the helpdesk.',
			31011001=>'Unknown acceptance code.',
			31021001=>'Invalid currency.',
			31031001=>'Acceptance code missing.',
			31041001=>'Inactive card.',
			31051001=>'Merchant not active.',
			31061001=>'Invalid expiry date.',
			31071001=>'Interrupted host communication.',
			31081001=>'Card refused.',
			31091001=>'Invalid password.',
			31101001=>'Plafond transaction (majoré du bonus) dépassé.',
			31111001=>'Plafond mensuel (majoré du bonus) dépassé.',
			31121001=>'Plafond centre de facturation dépassé.',
			31131001=>'Plafond entreprise dépassé.',
			31141001=>'Code MCC du fournisseur non autorisé pour la carte.',
			31151001=>'Numéro SIRET du fournisseur non autorisé pour la carte.',
			31161001=>'This is not a valid online bank account.',
			32001004=>'A technical problem has occurred. Please try again.',
			32001105=>'A technical problem has occurred. Please contact the helpdesk.',
			34011001=>'Bezahlung mit RatePAY nicht möglich.',
			39991001=>'A technical problem has occurred. Please contact your acquirer\'s helpdesk.',
			40001001=>'A technical problem has occurred. Please try again.',
			40001002=>'A technical problem has occurred. Please try again.',
			40001003=>'A technical problem has occurred. Please try again.',
			40001004=>'A technical problem has occurred. Please try again.',
			40001005=>'A technical problem has occurred. Please try again.',
			40001006=>'A technical problem has occurred. Please try again.',
			40001007=>'A technical problem has occurred. Please try again.',
			40001008=>'A technical problem has occurred. Please try again.',
			40001009=>'A technical problem has occurred. Please try again.',
			40001010=>'A technical problem has occurred. Please try again.',
			40001011=>'A technical problem has occurred. Please contact the helpdesk.',
			40001012=>'There has been a connection error with the receiving bank. Please try later or choose another payment method.',
			40001013=>'A technical problem has occurred. Please contact the helpdesk.',
			40001016=>'A technical problem has occurred. Please contact the helpdesk.',
			40001018=>'A technical problem has occurred. Please try again.',
			40001019=>'Sorry, an error has occurred during processing. Please retry the transaction (using the Back button of the browser). If the problem persists, contact your merchant\'s helpdesk.',
			40001020=>' Sorry, an error occurred during processing. Please retry the operation (using the Back button of the browser). If the problem persists, please contact your merchant\'s helpdesk.',
			40001050=>'A technical problem has occurred. Please contact the helpdesk.',
			40001133=>'Authentication failed. Incorrect signature for your bank\'s access control server.',
			40001134=>'Authentication failed. Please retry or cancel.',
			40001135=>'Authentication temporarily unavailable. Please retry or cancel.',
			40001136=>'Technical problem with your browser. Please retry or cancel.',
			40001137=>'Your bank is temporarily unavailable. Please try again later or choose another payment method.',
			40001998=>'Temporary technical problem. Please retry later.',
			50001001=>'Unknown card type.',
			50001002=>'Card number format check failed for given card number.',
			50001003=>'Merchant data error.',
			50001004=>'Merchant identification missing.',
			50001005=>'Expiry date error.',
			50001006=>'Amount is not a number.',
			50001007=>'A technical has problem occurred. Please contact the helpdesk.',
			50001008=>'A technical has problem occurred. Please contact the helpdesk.',
			50001009=>'A technical has problem occurred. Please contact the helpdesk.',
			50001010=>'A technical has problem occurred. Please contact the helpdesk.',
			50001011=>'Brand not supported for that merchant.',
			50001012=>'A technical has problem occurred. Please contact the helpdesk.',
			50001013=>'A technical has problem occurred. Please contact the helpdesk.',
			50001014=>'A technical has problem occurred. Please contact the helpdesk.',
			50001015=>'Invalid currency code.',
			50001016=>'A technical has problem occurred. Please contact the helpdesk.',
			50001017=>'A technical has problem occurred. Please contact the helpdesk.',
			50001018=>'A technical has problem occurred. Please contact the helpdesk.',
			50001019=>'A technical has problem occurred. Please contact the helpdesk.',
			50001020=>'A technical has problem occurred. Please contact the helpdesk.',
			50001021=>'A technical has problem occurred. Please contact the helpdesk.',
			50001022=>'A technical has problem occurred. Please contact the helpdesk.',
			50001023=>'A technical has problem occurred. Please contact the helpdesk.',
			50001024=>'A technical has problem occurred. Please contact the helpdesk.',
			50001025=>'A technical has problem occurred. Please contact the helpdesk.',
			50001026=>'A technical has problem occurred. Please contact the helpdesk.',
			50001027=>'A technical has problem occurred. Please contact the helpdesk.',
			50001028=>'A technical has problem occurred. Please contact the helpdesk.',
			50001029=>'A technical has problem occurred. Please contact the helpdesk.',
			50001030=>'A technical has problem occurred. Please contact the helpdesk.',
			50001031=>'A technical has problem occurred. Please contact the helpdesk.',
			50001032=>'A technical has problem occurred. Please contact the helpdesk.',
			50001033=>'A technical has problem occurred. Please contact the helpdesk.',
			50001034=>'A technical has problem occurred. Please contact the helpdesk.',
			50001035=>'A technical problem has occurred. Please contact the helpdesk.',
			50001036=>'Incorrect card length for the brand.',
			50001037=>'Purchasing card number for a standard merchant.',
			50001038=>'You should use a purchasing card for this transaction.',
			50001039=>'Details sent for a non-purchasing card merchant. Please contact the helpdesk.',
			50001040=>'Details not sent for a purchasing card transaction. Please contact the helpdesk.',
			50001041=>'Payment detail validation failed.',
			50001042=>'Sum of given transaction amounts (tax, discount, delivery, net, etc.) does not match total.',
			50001043=>'A technical problem has occurred. Please contact the helpdesk.',
			50001044=>'No acquirer configured for this operation.',
			50001045=>'No UID configured for this operation.',
			50001046=>'Operation not permitted for the merchant.',
			50001047=>'A technical problem has occurred. Please contact the helpdesk.',
			50001048=>'A technical problem has occurred. Please contact the helpdesk.',
			50001049=>'A technical problem has occurred. Please contact the helpdesk.',
			50001050=>'A technical problem has occurred. Please contact the helpdesk.',
			50001051=>'A technical problem has occurred. Please contact the helpdesk.',
			50001052=>'A technical problem has occurred. Please contact the helpdesk.',
			50001053=>'A technical problem has occurred. Please contact the helpdesk.',
			50001054=>'Card number incorrect or incompatible.',
			50001055=>'A technical problem has occurred. Please contact the helpdesk.',
			50001056=>'A technical problem has occurred. Please contact the helpdesk.',
			50001057=>'A technical problem has occurred. Please contact the helpdesk.',
			50001058=>'A technical problem has occurred. Please contact the helpdesk.',
			50001059=>'A technical problem has occurred. Please contact the helpdesk.',
			50001060=>'A technical problem has occurred. Please contact the helpdesk.',
			50001061=>'A technical problem has occurred. Please contact the helpdesk.',
			50001062=>'A technical problem has occurred. Please contact the helpdesk.',
			50001063=>'Card Issue Number does not correspond to range or is not present.',
			50001064=>'Start Date invalid or not present.',
			50001066=>'Invalid CVC code format.',
			50001067=>'The merchant is not registered for 3D-Secure.',
			50001068=>'Invalid card number or account number (PAN).',
			50001069=>'Invalid CardID and Brand match.',
			50001070=>'The ECI value is either not supported or conflicts with other transaction data.',
			50001071=>'Incomplete TRN demat.',
			50001072=>'Incomplete PAY demat.',
			50001073=>'No demat APP.',
			50001074=>'Authorisation period expired.',
			50001075=>'VERRes was an error message.',
			50001076=>'DCP amount greater than authorisation amount.',
			50001077=>'Details negative amount.',
			50001078=>'Details negative quantity.',
			50001079=>'Could not decode/decompress received PARes (3D-Secure).',
			50001080=>'Received PARes was an error message from ACS (3D-Secure).',
			50001081=>'Received PARes format was invalid according to the 3DS specifications (3D-Secure).',
			50001082=>'PAReq/PARes reconciliation failure (3D-Secure).',
			50001084=>'Maximum amount reached.',
			50001087=>'This transaction requires authentication. Please check with your bank.',
			50001090=>'CVC missing at input, but CVC check requested.',
			50001091=>'Postcode missing at input, but postcode check requested.',
			50001092=>'Address missing at input, but Address check requested.',
			50001093=>'Partial capture not allowed.',
			50001095=>'Invalid date of birth.',
			50001096=>'Invalid commodity code.',
			50001097=>'The requested currency and brand are incompatible.',
			50001111=>'Data validation error.',
			50001113=>'This order has already been processed.',
			50001114=>'Error in accessing the pre-payment check page.',
			50001115=>'Request not received in secure mode.',
			50001116=>'Unknown IP address origin.',
			50001117=>'No IP address origin.',
			50001118=>'PSPID not found or incorrect.',
			50001119=>'Password incorrect or disabled due to number of errors.',
			50001120=>'Invalid currency.',
			50001121=>'Invalid number of decimals for the currency.',
			50001122=>'Currency not accepted by the merchant.',
			50001123=>'Card type not active.',
			50001124=>'Number of lines doesn\'t match the number of payments.',
			50001125=>'Format validation error.',
			50001126=>'Overflow in data capture requests for the original order.',
			50001127=>'Incorrect original order status.',
			50001128=>'missing authorisation code for unauthorised order.',
			50001129=>'Overflow in refunds requests.',
			50001130=>'Original order access error.',
			50001131=>'Original history item access error.',
			50001132=>'The selected Catalogue is empty.',
			50001133=>'Duplicate request.',
			50001134=>'Authentication failed. Please retry or cancel.',
			50001135=>'Authentication temporarily unavailable. Please retry or cancel.',
			50001136=>'Technical problem with your browser. Please retry or cancel.',
			50001137=>'Your bank is temporarily unavailable. Please try again later or choose another payment method.',
			50001150=>'Fraud Detection: technical error (invalid IP).',
			50001151=>'Fraud detection: technical error (IPCTY unknown or error).',
			50001152=>'Fraud detection: technical error (CCCTY unknown or error).',
			50001153=>'Overflow in redo-authorisation requests.',
			50001170=>'Dynamic BIN check failed.',
			50001171=>'Dynamic country check failed.',
			50001172=>'Amadeus signature error.',
			50001174=>'Cardholder Name is too long.',
			50001175=>'Name contains invalid characters.',
			50001176=>'Card number is too long.',
			50001177=>'Card number contains non-numeric info.',
			50001178=>'Card Number Empty.',
			50001179=>'CVC too long.',
			50001180=>'CVC contains non-numeric info.',
			50001181=>'Expiry date contains non-numeric info.',
			50001182=>'Invalid expiry month.',
			50001183=>'Expiry date must be in the future.',
			50001184=>'SHA Mismatch.',
			50001186=>'Operation not permitted.',
			50001187=>'Operation not permitted.',
			50001205=>'Missing mandatory fields in invoicing address.',
			50001206=>'Missing mandatory date of birth field.',
			50001207=>'Missing required shopping basket details.',
			50001208=>'Missing social security number.',
			50001209=>'Invalid country code.',
			50001210=>'Missing annual salary.',
			50001211=>'Missing gender.',
			50001212=>'Missing e-mail.',
			50001213=>'Missing IP address.',
			50001214=>'Missing part-payment campaign ID.',
			50001215=>'Missing invoice number.',
			50001216=>'The alias must be different to the card number.',
			50001217=>'Invalid details for shopping basket calculation.',
			50001218=>'No Refunds allowed.',
			50001220=>'Invalid format of phone number.',
			50001221=>'Invalid ZIP format.',
			50001222=>'Firstname or/and lastname missing.',
			50001223=>'Firstname and/or lastname format invalid.',
			50001224=>'The phone number is missing.',
			50001225=>'Invalid email format.',
			50001300=>'Wrong brand/payment method.',
			50001301=>'Wrong account number format.',
			50001302=>'RFP operation code is only permitted with scheduled payments.',
			50001303=>'RFP operation code not permitted for a Disputed payment.',
			50001304=>'RFP operation code not permitted - Unpaid amounts.',
			55555555=>'An error occurred.',
			60000001=>'account number unknown.',
			60000003=>'not credited dd-mm-yy.',
			60000005=>'name/number do not match.',
			60000007=>'account number blocked.',
			60000008=>'specific direct debit block.',
			60000009=>'account number WKA.',
			60000010=>'administrative reason.',
			60000011=>'account number expired.',
			60000012=>'no direct debit authorisation.',
			60000013=>'debit not approved.',
			60000014=>'double payment.',
			60000018=>'name/address/city not entered.',
			60001001=>'no original direct debit for revocation.',
			60001002=>'payer\'s account number format error.',
			60001004=>'payer\'s account at different bank.',
			60001005=>'payee\'s account at different bank.',
			60001006=>'payee\'s account number format error.',
			60001007=>'payer\'s account number blocked.',
			60001008=>'payer\'s account number expired.',
			60001009=>'payee\'s account number expired.',
			60001010=>'direct debit not possible.',
			60001011=>'creditor payment not possible.',
			60001012=>'payer\'s account number unknown WKA-number.',
			60001013=>'payee\'s account number unknown WKA-number.',
			60001014=>'WKA transaction not permitted.',
			60001015=>'revocation period expired.',
			60001017=>'incorrect revocation reason.',
			60001018=>'original run number not numeric.',
			60001019=>'payment ID incorrect.',
			60001020=>'amount not numeric.',
			60001021=>'zero amount not permitted.',
			60001022=>'negative amount not permitted.',
			60001023=>'payer and payee giro account number.',
			60001025=>'processing code (verwerkingscode) incorrect.',
			60001028=>'revocation not permitted.',
			60001029=>'guaranteed direct debit on giro account number.',
			60001030=>'NBC transaction type incorrect.',
			60001031=>'description too long.',
			60001032=>'book account number not issued.',
			60001034=>'book account number incorrect.',
			60001035=>'payer\'s account number not numeric.',
			60001036=>'payer\'s account number not eleven-proof.',
			60001037=>'payer\'s account number not issued.',
			60001039=>'payer\'s account number of DNB/BGC/BLA.',
			60001040=>'payee\'s account number not numeric.',
			60001041=>'payee\'s account number not eleven-proof.',
			60001042=>'payee\'s account number not issued.',
			60001044=>'payee\'s account number unknown.',
			60001050=>'payee\'s name missing.',
			60001051=>'indicate payee\'s bank account number instead of 3102.',
			60001052=>'no direct debit contract.',
			60001053=>'amount beyond limits.',
			60001054=>'selective direct debit block.',
			60001055=>'original run number unknown.',
			60001057=>'payer\'s name missing.',
			60001058=>'payee\'s account number missing.',
			60001059=>'restore not permitted.',
			60001060=>'bank\'s reference (navraaggegeven) missing.',
			60001061=>'BEC/GBK number incorrect.',
			60001062=>'BEC/GBK code incorrect.',
			60001087=>'book account number not numeric.',
			60001090=>'cancelled on request.',
			60001091=>'cancellation order executed.',
			60001092=>'cancelled instead of ended.',
			60001093=>'book account number is a shortened account number.',
			60001094=>'instructing party and payer account numbers do not match.',
			60001095=>'payee unknown GBK acceptor.',
			60001097=>'instructing party and payee account numbers do not match.',
			60001099=>'clearing not permitted.',
			60001101=>'payer\'s account number has no spaces.',
			60001102=>'PAN length not numeric.',
			60001103=>'PAN length outside limits.',
			60001104=>'track number not numeric.',
			60001105=>'track number not valid.',
			60001106=>'PAN sequence number not numeric.',
			60001107=>'domestic PAN not numeric.',
			60001108=>'domestic PAN not eleven-proof.',
			60001109=>'domestic PAN not issued.',
			60001110=>'foreign PAN not numeric.',
			60001111=>'card validity date not numeric.',
			60001112=>'book period number (boekperiodenr) not numeric.',
			60001113=>'transaction number not numeric.',
			60001114=>'transaction time not numeric.',
			60001115=>'invalid transaction time.',
			60001116=>'transaction date not numeric.',
			60001117=>'invalid transaction date.',
			60001118=>'STAN not numeric.',
			60001119=>'instructing party\'s name missing.',
			60001120=>'foreign amount (bedrag-vv) not numeric.',
			60001122=>'rate (verrekenkoers) not numeric.',
			60001125=>'number of decimals (aantaldecimalen) incorrect.',
			60001126=>'tariff (tarifering) not B/O/S.',
			60001127=>'domestic costs (kostenbinnenland) not numeric.',
			60001128=>'domestic costs (kostenbinnenland) not higher than zero.',
			60001129=>'foreign costs (kostenbuitenland) not numeric.',
			60001130=>'foreign costs (kostenbuitenland) not higher than zero.',
			60001131=>'domestic costs (kostenbinnenland) not zero.',
			60001132=>'foreign costs (kostenbuitenland) not zero.',
			60001134=>'Euro record not completed.',
			60001135=>'Customer currency incorrect.',
			60001136=>'NLG amount not numeric.',
			60001137=>'NLG amount not higher than zero.',
			60001138=>'NLG amount not equal to Amount.',
			60001139=>'NLG amount incorrectly converted.',
			60001140=>'EUR amount not numeric.',
			60001141=>'EUR amount not greater than zero.',
			60001142=>'EUR amount not equal to Amount.',
			60001143=>'EUR amount incorrectly converted.',
			60001144=>'Customer currency not NLG.',
			60001145=>'rate euro-vv (Koerseuro-vv) not numeric.',
			60001146=>'comma rate euro-vv (Kommakoerseuro-vv) incorrect.',
			60001147=>'invalid acceptgiro distributor.',
			60001148=>'Original run number and/or BRN missing.',
			60001149=>'Amount/Account number/ BRN different.',
			60001150=>'Direct debit already revoked/restored.',
			60001151=>'Direct debit already reversed/revoked/restored.',
			60001153=>'Payer\'s account number not know.',
		);
		
		
		if( isset($ncerorr_list[$code] ))
			return $ncerorr_list[$code];
		else
			return 'Unknown';
	}
	
	/**
	 * Generate Text Input HTML.
	 *
	 * @access public
	 * @param mixed $key
	 * @param mixed $data
	 * @since 1.0.0
	 * @return string
	 */
	public function generate_text_html( $key, $data ) {
		global $woocommerce;
	
		$html = '';
	
		$data['title']			= isset( $data['title'] ) ? $data['title'] : '';
		$data['disabled']		= empty( $data['disabled'] ) ? false : true;
		$data['class'] 			= isset( $data['class'] ) ? $data['class'] : '';
		$data['css'] 			= isset( $data['css'] ) ? $data['css'] : '';
		$data['placeholder'] 	= isset( $data['placeholder'] ) ? $data['placeholder'] : '';
		$data['type'] 			= isset( $data['type'] ) ? $data['type'] : 'text';
		$data['desc_tip']		= isset( $data['desc_tip'] ) ? $data['desc_tip'] : false;
		$data['description']    = isset( $data['description'] ) ? $data['description'] : '';
		$data['append']    		= isset( $data['append'] ) ? $data['append'] : '';
		$data['default']		= isset( $data['default'] ) ? $data['default'] : '';
		
		$val = esc_attr($this->get_option( $key ));
		
		if( isset($val) and $val !='' )
			$dflt = $val;
		else
			$dflt = $data['default'];
	
		// Description handling
		if ( $data['desc_tip'] === true ) {
			$description = '';
			$tip         = $data['description'];
		} elseif ( ! empty( $data['desc_tip'] ) ) {
			$description = $data['description'];
			$tip         = $data['desc_tip'];
		} elseif ( ! empty( $data['description'] ) ) {
			$description = $data['description'];
			$tip         = '';
		} else {
			$description = $tip = '';
		}
	
		// Custom attribute handling
		$custom_attributes = array();
	
		if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) )
			foreach ( $data['custom_attributes'] as $attribute => $attribute_value )
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
	
		$html .= '<tr valign="top">' . "\n";
		$html .= '<th scope="row" class="titledesc">';
		$html .= '<label for="' . esc_attr( $this->plugin_id . $this->id . '_' . $key ) . '">' . wp_kses_post( $data['title'] ) . '</label>';
	
		if ( $tip )
			$html .= '<img class="help_tip" data-tip="' . esc_attr( $tip ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />';
	
		$html .= '</th>' . "\n";
		$html .= '<td class="forminp">' . "\n";
		$html .= '<fieldset><legend class="screen-reader-text"><span>' . wp_kses_post( $data['title'] ) . '</span></legend>' . "\n";
		$html .= '<input class="input-text regular-input ' . esc_attr( $data['class'] ) . '" type="' . esc_attr( $data['type'] ) . '" name="' . esc_attr( $this->plugin_id . $this->id . '_' . $key ) . '" id="' . esc_attr( $this->plugin_id . $this->id . '_' . $key ) . '" style="' . esc_attr( $data['css'] ) . '" value="' .  $dflt  . '" placeholder="' . esc_attr( $data['placeholder'] ) . '" ' . disabled( $data['disabled'], true, false ) . ' ' . implode( ' ', $custom_attributes ) . ' />';
	
		if ( $description )
			$html .= ' <p class="description">' . wp_kses_post( $description ) . '</p>' . "\n";
		$html .= $data['append'];
		$html .= '</fieldset>';
		$html .= '</td>' . "\n";
		$html .= '</tr>' . "\n";
	
		return $html;
	}
	/**
	 * Validate Text Field.
	 *
	 * Make sure the data is escaped correctly, etc.
	 *
	 * @access public
	 * @param mixed $key
	 * @since 1.0.0
	 * @return string
	 */
	public function validate_text_field( $key ) {
		$text = $this->get_option( $key );
	
		if ( isset( $_POST[ $this->plugin_id . $this->id . '_' . $key ] ) ) {
			
			$text = trim( stripslashes( $_POST[ $this->plugin_id . $this->id . '_' . $key ] ) );
			if( preg_match('/#[0-9a-fA-F]{3,6}/', $text) ){
				return $text;
			}
			else
				$text = esc_attr( $text );
			
		}
	
		return $text;
	}
}
