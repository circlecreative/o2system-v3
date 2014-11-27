$(document).ready(function(){
	$('.selectpicker').selectpicker();

	toastr.options = {
	  "closeButton": true,
	  "debug": false,
	  "positionClass": "toast-top-right",
	  "onclick": null,
	  "showDuration": "300",
	  "hideDuration": "1000",
	  "timeOut": "5000",
	  "extendedTimeOut": "1000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	}

	function clearState()
	{
		$('tr.highlight').each(function(){
			$(this).removeClass('highlight');
		});

		$('.checkbox').each(function(){
			$(this).removeClass('checked');
		});
	}

	$('form').on('submit', function( event ) {
		event.preventDefault();
		var iSubmit = $(this).serializeArray();

		$.each(iSubmit, function(i,input){
			if(input.value != '' && input.value != 'all') {
				if(input.name == 'date-type') {
					input.value = '';

					$('input[name="date-type"]').each(function(){
						if ( $(this).closest('.checkbox').hasClass('checked') ) {
							input.value = input.value + $(this).val() + ',';
						}
					});

					input.value.substring( 0, (input.value.length-1) );
				}

				if(input.name == 'fields') {
					input.value = '';

					$('input[name="fields"]').each(function(){
						if ( $(this).closest('.checkbox').hasClass('checked') ) {
							input.value = input.value + $(this).val() + ',';
						}
					});

					input.value.substring( 0, (input.value.length-1) );
				}

				$.SERVER.GET[ input.name ] = input.value;
			}

			if(input.name == 'search' && input.value != '') {
				$.SERVER.GET[ 'page' ] = 1;
			}
		});

		//console.log( $.SERVER.SELF_URL + $.http_build_query());

		window.location.href = $.SERVER.SELF_URL + $.http_build_query();
	});

	$('select[name="entries"], select[name="status"]').change(function(){
		if($(this).val !== 'undefined') {
			$.SERVER.GET[ $(this).attr('name') ] = $(this).val();

			if($(this).attr('name') == 'entries') {
				$.SERVER.GET[ 'page' ] = 1;
			}

			window.location.href = $.SERVER.SELF_URL + $.http_build_query();
		}
	});
});