/**
 */
$(function(){
	$('.addButton').live({
		click:function(e){
			e.preventDefault();
			//console.log($(this).attr('id'));
			var ro_class = $(this).attr('id');
			$('#AddNewDS_confirm').attr('ro_class', ro_class);
			$('#AddNewDS_confirm').html('Add New '+ro_class);
			$("#AddNewDS").modal('show');
			Core_bindFormValidation($('#AddNewDS form'));
		}
	});	
    $('input[name=key]').on({
    	blur: function(e){
    		log('st')
    		e.preventDefault();
    		$('.alert-error').hide();
    	}
    });

	$('#generate_random_key').live({
		click:function(e){
			e.preventDefault();
			var input = $(this).prev('input');
			$.ajax({
				type: 'GET',
				url: base_url+'services/registry/get_random_key/',
				success:function(data){
					$('.alert-error').hide();
					$(input).val(data.key)
				},
				error:function(data){
					console.log(data.responseText);
				}
			});
		}
	});

	$('#AddNewDS_confirm').die().live({
		click:function(e){
			e.preventDefault();
			if(Core_checkValidForm($('#AddNewDS form'))){
				registry_object_key = $('input[name=key]').val();
				ro_class = $(this).attr('ro_class');
				type = $('input[name=type]').val();
				group = $('input[name=group]').val();
				data_source_id = $('select[name=data_source_id]').val();
				originating_source = $('input[name=originatingSource]').val();

				var data = {data_source_id:data_source_id, registry_object_key:registry_object_key, ro_class:ro_class, type:type, group:group, originating_source:originating_source};
				$.ajax({
					type: 'POST',
					url: base_url+'registry_object/add_new',
					data:{data:data},
					dataType:'JSON',
					success:function(data){
						if(data.status != 'ERROR')
						{
							$("#AddNewDS").modal('hide');
							if(data.success) window.location = base_url+'registry_object/edit/'+data.ro_id+'#!/advanced/admin';
						}
						else{
							$('.alert-error').html(data.message);
							$('.alert-error').show();
						}
					},
					error:function(data){
						console.log("error: " + data);
					}
				});
			}
		}
	});


});