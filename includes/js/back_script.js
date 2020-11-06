jQuery( document ).ready(function() {
	jQuery('input[name="amount_choice"]').change(function(){
		var value = jQuery( 'input[name="amount_choice"]:checked' ).val();
		if(value == "custom"){
			jQuery('input[name="amount"]').css('display','block');
			jQuery('input[name="fieldamount"]').css('display','none');
		}else{
			jQuery('input[name="amount"]').css('display','none');
			jQuery('input[name="fieldamount"]').css('display','block');
		}
	});



	var value = jQuery( 'input[name="amount_choice"]:checked' ).val();
	if(value == "custom"){
		jQuery('input[name="amount"]').css('display','block');
		jQuery('input[name="fieldamount"]').css('display','none');
	}else{
		jQuery('input[name="amount"]').css('display','none');
		jQuery('input[name="fieldamount"]').css('display','block');
	}



	var qty_choice = jQuery( 'input[name="qty_choice"]:checked' ).val();
	if(qty_choice == "custom"){
		jQuery('input[name="quantity"]').css('display','block');
		jQuery('input[name="fieldquantity"]').css('display','none');
	}else{
		jQuery('input[name="quantity"]').css('display','none');
		jQuery('input[name="fieldquantity"]').css('display','block');
	}


	jQuery('input[name="qty_choice"]').change(function(){
		var qty_choice = jQuery( 'input[name="qty_choice"]:checked' ).val();
		if(qty_choice == "custom"){
			jQuery('input[name="quantity"]').css('display','block');
			jQuery('input[name="fieldquantity"]').css('display','none');
		}else{
			jQuery('input[name="quantity"]').css('display','none');
			jQuery('input[name="fieldquantity"]').css('display','block');
		}
	});
});