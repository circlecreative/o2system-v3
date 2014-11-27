$(document).ready(function(){
	$('.selectpicker').selectpicker();

	// Switch
	$.fn.bootstrapSwitch.defaults.size = 'mini';
	$('.switch').bootstrapSwitch();
	$('.switch').on('switchChange.bootstrapSwitch', function(event, state) {
		var iAction = (state == true ? 'publish' : 'unpublish');
		var iData = new Array();
		iData.push($(this).attr('data-id'));

		$.post($.SERVER.SELF_URL,{
			'status' : iAction,
			'id' : iData,
		}, function(response){
			
		},'json');
	});

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

	$('.datatables').on('click','tr', function(){
		if ( $(this).hasClass('highlight') ) {
      		clearState();
		}
		else {
			$(this).find('.checkbox').addClass('checked');
      		$(this).addClass('highlight');
		}
	});

	$('.datatables').on('change','.dt-checkbox', function(){
		if ( $(this).closest('.checkbox').hasClass('checked') ) {
			$('.dt-checkbox').closest('.checkbox').addClass('checked');
			$('.dt-data-checkbox').each(function(){
				$(this).closest('.checkbox').addClass('checked');
      			$(this).closest('tr').addClass('highlight');
			});
		}
		else {
			clearState();
		}
	});

	$('.datatables').on('change','.dt-data-checkbox', function(){
		if ( $(this).parents('.checkbox').hasClass('checked') ) {
			$(this).closest('.checkbox').addClass('checked');
      		$(this).closest('tr').addClass('highlight');
		}
		else {
			clearState();
		}
	});

	$('.datatables-tools').on('click','.btn-tools', function(){
		var iAction = $(this).attr('action');
		var URL = $(this).attr('href');
		var iData = new Array();
		var iFind = false;

		if(iAction == 'edit') {
			$('.dt-data-checkbox').each(function(){
				if ( $(this).closest('.checkbox').hasClass('checked') ) {
					window.location.href = $.SERVER.SELF_URL.replace('datatable','') + '/edit/' +  $(this).val() + '.html';
					iFind = true;
				}
			});
		} else if(iAction !== 'undefined') {
			$('.dt-data-checkbox').each(function(){
				if ( $(this).closest('.checkbox').hasClass('checked') ) {
					iData.push($(this).val());
					iFind = true;
				}
			});

			if(iData.length > 0) {
				$.post($.SERVER.SELF_URL,{
					'status' : iAction,
					'id' : iData,
				}, function(response){
					if(response.success) {
						if(response.remove == true) {
							$.each(iData, function(i, iDataID){
								$('tr.highlight').remove();
							});

							var iTR =  $('tr[data-id*=""]');
							if(iTR.length == 0) {
								$('tr.dt-empty').removeClass('hidden').show();
							}
						}

						if(iAction == 'publish') {
							$('tr.highlight').each(function(){
								$(this).find('.switch').attr('checked',true);
								$(this).find('.bootstrap-switch').removeClass('bootstrap-switch-off').addClass('bootstrap-switch-on');
							});
						}

						if(iAction == 'unpublish') {
							$('tr.highlight').each(function(){
								$(this).find('.switch').removeAttr('checked');
								$(this).find('.bootstrap-switch').removeClass('bootstrap-switch-on').addClass('bootstrap-switch-off');
							});
						}
					}

					clearState();
				},'json');
			}
		}

		if(iFind == false && iAction !== 'edit' && iAction !== 'undefined') {
			$.SERVER.GET['status'] = iAction; 
			window.location.href = $.SERVER.SELF_URL + $.http_build_query();
		} else if(iFind == false && typeof iAction !== 'undefined') {
			toastr.error('Please choose one data');
		}
	});

	$('body').on('keyup','input[name="page"]',function(){
		iVal = $(this).val();

		$('input[name="page"]').each(function(){
			$(this).attr('value',iVal);
		});
	});

	$('.dt-goto').keypress(function(event){
		if (event.keyCode == 10 || event.keyCode == 13){
			event.preventDefault();
			var iSubmit = $('form').serializeArray();

			$.each(iSubmit, function(i,input){
				if(input.value != '' && input.value != 'all') {
					$.SERVER.GET[ input.name ] = input.value;
				}

				if(input.name == 'search' && input.value != '') {
					$.SERVER.GET[ 'page' ] = 1;
				}
			});

			window.location.href = $.SERVER.SELF_URL + $.http_build_query();
		}
	});

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

	var iOrderNumber = $('[data-toggle="datatable"]>tbody>tr').first().attr('data-number');
	iOrderNumber = Number(iOrderNumber);

	var iOrderingCurrent = new Array();
	var iOrderingCurrentSequence = new Array();

	$('.datatables tbody tr').each(function(i){
    	iOrderingCurrent[$(this).attr('data-id')] = $(this).find('.dt-ordering').val();
    	iOrderingCurrentSequence[$(this).attr('data-id')] = iOrderNumber+i;
    });

	var iOrderingModified = new Array();

	var fixHelperModified = function(e, tr) {
	    var iTR = tr.children();
	    var iTRClone = tr.clone();
	    iTRClone.children().each(function(index) {
	        $(this).width(iTR.eq(index).width())
	    });
	    return iTRClone;
	},

    updateIndex = function(e, ui) {
    	var iDataID = ui.item.find('.dt-ordering').attr('data-id');
    	var iParentID = ui.item.find('.dt-ordering').attr('data-parent');
    	var iParentSequence = iOrderingCurrentSequence[iParentID];
    	var iPrevParentID = ui.item.prev().find('.dt-ordering').attr('data-parent');

    	$('td.index', ui.item.parent()).each(function (i) {
            $(this).html(i + 1);
        });

    	$('.datatables tbody tr').each(function(i){
        	var iVal = iOrderNumber+i;
        	$(this).find('.dt-ordering').val(iVal);
        	iOrderingModified[$(this).attr('data-id')] = iVal;
        });

        var iDataSequence = iOrderingModified[iDataID];

    	if(iParentID != 0 && iDataSequence <= iParentSequence) {
	    	$('.datatables tbody').sortable('cancel');
	    	delete iOrderingModified;
	    } else if(iParentID == 0) {
	    	var iChildrens = $('.dt-ordering[data-parent="'+iDataID+'"]').closest('tr');
	    	$.each(iChildrens.get().reverse(), function(i){
	    		var iTRClone = $(this).clone();
	    		$(this).remove();
	    		$(iTRClone).insertAfter(ui.item);
	    	});

	    	$('.datatables tbody tr').each(function(i){
	        	var iVal = iOrderNumber+i;
	        	$(this).find('.dt-ordering').val(iVal);
	        	iOrderingModified[$(this).attr('data-id')] = iVal;
	        });
	    }
    };

	$('.datatables tbody.sortable').sortable({
	    helper: fixHelperModified,
	    stop: updateIndex
	}).disableSelection();

	$('.btn-ordering[action="save"]').click(function(){
		$.post($.SERVER.SELF_URL, {
			ordering : iOrderingModified
		}, function(response){
			if(response.success == true) {
				toastr.success(response.message);
			}
		}, 'json');
	});

	$('.btn-ordering[action="cancel"]').click(function(){
		$('.datatables tbody').sortable('cancel');

		$(iOrderingCurrent).each(function(dataID, ordering){
        	$('tr[data-id="'+dataID+'"]').find('.dt-ordering').val(ordering);
        });
	});
});