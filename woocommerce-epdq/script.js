jQuery(document).ready(function(){
			
			//	1.	title
			jQuery('#woocommerce_epdq_checkout_TITLE').on('blur',function(){
				jQuery('#sumutitle').text(jQuery(this).attr('value'));
			});

			//	2.	body bg color
			jQuery('#woocommerce_epdq_checkout_BGCOLOR').on('blur',function(){
				jQuery('#simubody').css({'background-color':jQuery(this).attr('value')});
			});

			//	3.	body text color
			jQuery('#woocommerce_epdq_checkout_TXTCOLOR').on('blur',function(){				
				jQuery('#simubody').css({'color':jQuery(this).attr('value')});
			});

			//	4.	table bg color
			jQuery('#woocommerce_epdq_checkout_TBLBGCOLOR').on('blur',function(){				
				jQuery('td.ncolh1,td.ncoltxtl,td.ncoltxtr,td.ncoltxtc,td.ncollogol,td.ncollogor,td.ncoltxtmessage,td.ncolinput,td.ncolline1,td.ncolline2,table.ncoltable1,table.ncoltable2,table.ncoltable3,table.ncoltable1 td,table.ncoltable2 td,table.ncoltable3 td').css({'background-color':jQuery(this).attr('value')});
				
			});

			//	5.	table text color
			jQuery('#woocommerce_epdq_checkout_TBLTXTCOLOR').on('blur',function(){
				jQuery('td.ncolh1,td.ncoltxtl,td.ncoltxtr,td.ncoltxtc,td.ncollogol,td.ncollogor,td.ncoltxtmessage,td.ncolinput,td.ncolline1,td.ncolline2,table.ncoltable1,table.ncoltable2,table.ncoltable3,table.ncoltable1 td,table.ncoltable2 td,table.ncoltable3 td, #simubldy a,#simubldy p').css({'color':jQuery(this).attr('value')});
				jQuery('table.ncoltable1,table.ncoltable2,table.ncoltable3').css({'border':'1px solid '+jQuery(this).attr('value')});
			});

			//	6.	button bg color
			jQuery('#woocommerce_epdq_checkout_BUTTONBGCOLOR').on('blur',function(){				
				jQuery('input.ncol').css({'background-color':jQuery(this).attr('value')});
			});

			//	7.	button text color
			jQuery('#woocommerce_epdq_checkout_BUTTONTXTCOLOR').on('blur',function(){
				jQuery('input.ncol').css({'color':jQuery(this).attr('value')});
			});

			//	8.	font
			jQuery('#woocommerce_epdq_checkout_FONTTYPE').on('blur',function(){
				jQuery('td.ncolh1,td.ncoltxtl,td.ncoltxtr,td.ncoltxtc,td.ncollogol,td.ncollogor,td.ncoltxtmessage,td.ncolinput,td.ncolline1,td.ncolline2,table.ncoltable1 td,table.ncoltable2 td,table.ncoltable3 td,input.ncol').css({'font-family':jQuery(this).attr('value')});
			});

			//	9.	logo
			jQuery('#woocommerce_epdq_checkout_LOGO').on('blur',function(){});
			
			var $color_inputs = jQuery('input.popup-colorpicker');
			$color_inputs.each(function(){
				var $input = jQuery(this);
				var $pickerId = "#" + jQuery(this).attr('id') + "picker";
				jQuery($pickerId).hide();
								
				jQuery($pickerId).farbtastic($input);
				

				jQuery($input).click(function(){jQuery($pickerId).slideToggle();});
			});

			jQuery('a[rel=simulation]').click(function(e){
				e.preventDefault;
				alert('yahoo!!');
				return false;
			});

			jQuery('a[rel=update]').click(function(e){
				e.preventDefault;
				
				jQuery('#demographicDisplay').addClass('loading');
				jQuery('.blockUI').show();

				jQuery('#sumutitle').text(jQuery('#woocommerce_epdq_checkout_TITLE').attr('value'));
				jQuery('#simubody').css({'color':jQuery('#woocommerce_epdq_checkout_TXTCOLOR').attr('value')});
				//	body bg
				jQuery('#simubody').css({'background-color':jQuery('#woocommerce_epdq_checkout_BGCOLOR').attr('value')});
				//	table bg
				jQuery('td.ncolh1,td.ncoltxtl,td.ncoltxtr,td.ncoltxtc,td.ncollogol,td.ncollogor,td.ncoltxtmessage,td.ncolinput,td.ncolline1,td.ncolline2,table.ncoltable1,table.ncoltable2,table.ncoltable3,table.ncoltable1 td,table.ncoltable2 td,table.ncoltable3 td').css({'background-color':jQuery('#woocommerce_epdq_checkout_TBLBGCOLOR').attr('value')});
				//	tbltext
				jQuery('td.ncolh1,td.ncoltxtl,td.ncoltxtr,td.ncoltxtc,td.ncollogol,td.ncollogor,td.ncoltxtmessage,td.ncolinput,td.ncolline1,td.ncolline2,table.ncoltable1,table.ncoltable2,table.ncoltable3,table.ncoltable1 td,table.ncoltable2 td,table.ncoltable3 td, #simubldy a,#simubldy p').css({'color':jQuery('#woocommerce_epdq_checkout_TBLTXTCOLOR').attr('value')});
				jQuery('table.ncoltable1,table.ncoltable2,table.ncoltable3').css({'border':'1px solid '+jQuery('#woocommerce_epdq_checkout_TBLTXTCOLOR').attr('value')});
				//button text
				jQuery('input.ncol').css({'color':jQuery('#woocommerce_epdq_checkout_BUTTONTXTCOLOR').attr('value')});
				//button bg
				jQuery('input.ncol').css({'background-color':jQuery('#woocommerce_epdq_checkout_BUTTONBGCOLOR').attr('value')});
				//font
				jQuery('td.ncolh1,td.ncoltxtl,td.ncoltxtr,td.ncoltxtc,td.ncollogol,td.ncollogor,td.ncoltxtmessage,td.ncolinput,td.ncolline1,td.ncolline2,table.ncoltable1 td,table.ncoltable2 td,table.ncoltable3 td,input.ncol').css({'font-family':jQuery('#woocommerce_epdq_checkout_FONTTYPE').attr('value')});
				
				jQuery('.blockUI').hide();
				jQuery('#demographicDisplay').removeClass('loading');
								
				return false;
			});		
			
			
		});		
		
(function($){
	"use strict";
	$(function(){
		if( jQuery('#demographicDisplay').hasClass('loading') ){
			jQuery('a[rel=update]').trigger('click');
			jQuery('#demographicDisplay').removeClass('loading');
			jQuery('.blockUI').hide();
		}
	});
	
})(jQuery);