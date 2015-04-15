$(document).ready(function() {
	
	// the same function for both forms
	$('#early-access-form, #message-form').submit(function() {
		var form = $(this);
		$.ajax({
			'method': 'post',
			'url': '/ajax.php',
			'data': form.serialize(),
			'success': function(data) {
				data = $.parseJSON(data);
				form.find('.form-info').hide().removeClass('alert-success').removeClass('alert-error');
				if(data.status == 'success')
				{
					form.find('.form-info').addClass('alert-success').html(data.message).show();
				}
				else
				{
					form.find('.form-info').addClass('alert-error').html(data.message).show();
				}
			}
		});
		
		return false;
	});
});