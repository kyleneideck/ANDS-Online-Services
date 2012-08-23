/**
 * Core Data Source Javascript
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */

$(function(){

	/*
	 * suffix is determined in footer.php
	 * Example: #!/browse/lists/
	 * 			#!/view/115
	 *			#!/edit/115
	 *			#!/delete/115
	 */

	$(window).hashchange(function(){
		var hash = location.hash;
		if(hash.indexOf(suffix)==0){//if the hash starts with a particular suffix
			var words = hash.substring(suffix.length, hash.length).split('/');
			var action = words[0];//action will be the first word found
			try{
				$('section').hide();
				switch(action){
					case 'browse' : browse(words[1]);break;
					case 'view': load_datasource(words[1]);break;
					case 'edit': load_datasource_edit(words[1], words[2]);break;
					case 'delete': load_datasource_delete(words[1]);break;
					default: logErrorOnScreen('this functionality is currently being worked on');break;
				}
				$('#data_source_view_container').attr('data_source_id', words[1]);
			}catch(error){
				var template = $('#error-template').html();
				var output = Mustache.render(template, error);
				$('#main-content').append(output);
				$('section').hide();
			}
		}else{//there is no hash suffix
			browse('lists');
		}
	});
	$(window).hashchange();
});

function browse(view){
	if(view=='thumbnails' || view=='lists'){
		$('section').hide();
		$('#items').removeClass();
		$('#items').addClass(view);
		$('#browse-datasources').slideDown();
	}else{
		logErrorOnScreen('invalid View Argument');
	}
	$("#datasource-chooser").chosen();
}

var currentView = 'thumbnails';
$('#switch_view a').click(function(){
	changeHashTo('browse/'+$(this).attr('name'));
	currentView = $(this).attr('name');
});

function load_more(page){
	$.ajax({
		url: 'data_source/getDataSources/'+page,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			var itemsTemplate = $('#items-template').html();
			var output = Mustache.render(itemsTemplate, data);
			$('#items').append(output);
		}
	});
}
load_more(1);
$('#load_more').click(function(){
	var page = parseInt($(this).attr('page'));
	page++;
	load_more(page);
	$(this).attr('page', page++);
});

$('.item').live({
	mouseenter: function(e){
		$('.btn-group', this).show();
	},
	mouseleave: function(e){
		$('.btn-group', this).hide();
	},
	dblclick: function(e){
		e.preventDefault();
		changeHashTo('view/'+$(this).attr('data_source_id'));
	},
	click: function(){
		
	}
});

$('.item-control .btn').live({
	click: function(e){
		e.preventDefault();
		var data_source_id = $(this).parent().parent().attr('data_source_id');
		if($(this).hasClass('view')){
			changeHashTo('view/'+data_source_id);
		}else if($(this).hasClass('edit')){
			changeHashTo('edit/'+data_source_id);
		}else if($(this).hasClass('delete')){
			changeHashTo('delete/'+data_source_id);
		}
	}
});

$('#toggle_side_bar_btn').live({
	click: function(e){
		e.preventDefault();
		$('#browse-datasources-left').toggleClass('span8', 200).toggleClass('span12', 100);
		$('#browse-datasources-right').toggle();
		$(this).qtip().hide();
	}
});

$('#datasource-chooser').live({
	change: function(e){
		changeHashTo('view/'+$(this).val());
	}
});

$('.box-header .close').live({
	click: function(e){
		//changeHashTo('browse/'+currentView);
		window.history.back();
	}
});

/*
 * Load a datasource view
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return false
 */
function load_datasource(data_source_id){
	$('#view-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$.ajax({
		url: 'data_source/getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);
			var template = $('#data-source-view-template').html();
			var output = Mustache.render(template, data);
			var view = $('#view-datasource');
			$('#view-datasource').html(output);
			$('#view-datasource').fadeIn(500);

			$('.btn-group button', view).click(function(){
				var data_source_id = $(this).parent().attr('data_source_id');
				if($(this).hasClass('edit')){
					changeHashTo('edit/'+data_source_id);
				}else if($(this).hasClass('history')){
					changeHashTo('history/'+data_source_id);
				}else if($(this).hasClass('delete')){
					changeHashTo('deleteRecord/'+data_source_id);
				}
			});

			drawCharts();

			$('#view-datasource  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:false
				});
			});

			$('.ro-list li').click(function(){
				var type = $(this).attr('type');
				var name = $(this).attr('name');
				var url_to = base_url+'registry_object/manage/'+data_source_id+'/'+suffix+'browse/thumbnails/'+type+'='+name;
				window.location = url_to;
			});
		}
	});
	return false;
}

function drawCharts(){
	$('#ro-progression').height('350').html('');
	$.jqplot('ro-progression',  [[[1, 2],[3,5.12],[5,13.1],[7,33.6],[9,85.9],[11,219.9]]]);
}

/*
 * Load a datasource edit view (redundancy)
 * @TODO: refactor
 * With animation, slide the view into place, 
 * hide the browse section and hide other section in progress
 * @params data_source_id
 * @return false
 */
function load_datasource_edit(data_source_id, active_tab){
	$('#edit-datasource').html('Loading');
	$('#browse-datasources').slideUp(500);
	$('#view-datasources').slideUp(500);
	$.ajax({
		url: 'getDataSource/'+data_source_id,
		type: 'GET',
		contentType: 'application/json; charset=utf-8',
		dataType: 'json',
		success: function(data){
			//console.log(data);
			var template = $('#data-source-edit-template').html();
			var output = Mustache.render(template, data);
			$('#edit-datasource').html(output);
			$('#edit-datasource').fadeIn(500);
			if(active_tab && $('#'+active_tab).length > 0){//if an active tab is specified and exists
				$('.nav-tabs li a[href=#'+active_tab+']').click();
			}
			
			$('#edit-datasource  .normal-toggle-button').each(function(){
				if($(this).attr('value')=='t' || $(this).attr('value')=='1' || $(this).attr('value')=='true' ){
					$(this).find('input').attr('checked', 'checked');
				}
				$(this).toggleButtons({
					width:75,enable:true,
					onChange:function(){
						$(this).find('input').attr('checked', 'checked');
					}
				});
			});
			
			$("#edit-datasource .chzn-select").chosen().change(function(){
				var input = $('#'+$(this).attr('for'));
				$(input).val($(this).val());
			});
			$('#edit-datasource .chzn-select').each(function(){
				var input = $('#'+$(this).attr('for'));
				$(this).val($(input).val());
				$(this).chosen().trigger("liszt:updated");
			});
		}
	});
	return false;
}
$('#save-edit-form').live({
	click: function(e){
		e.preventDefault();
		var jsonData = [];
		$(this).button('loading');
		
		jsonData.push({name:'data_source_id', value:$('#data_source_view_container').attr('data_source_id')});
		
		$('#edit-datasource #edit-form input, #edit-datasource #edit-form textarea').each(function(){
			var label = $(this).attr('name');
			var value = $(this).val();
			if($(this).attr('type')=='checkbox'){
				var label = $(this).attr('for');
				var value = $(this).is(':checked');
			}
			if(value!='' && value){
				jsonData.push({name:label, value:value});
			}
		});

		$.ajax({
			url:'data_source/updateDataSource', 
			type: 'POST',
			data: jsonData,
			success: function(data){
				
					if (!data.status == "OK")
					{
						$('#myModal').modal();
						logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
						$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
					}
					else
					{
						changeHashTo('view/'+$('#data_source_view_container').attr('data_source_id'));
						createGrowl("Your Data Source was successfully updated");
						updateGrowls();
					}
			},
			error: function()
			{
				$('#myModal').modal();
				logErrorOnScreen("An error occured whilst saving your changes!", $('#myModal .modal-body'));
				$('#myModal .modal-body').append("<br/><pre>" + data + "</pre>");
			}
		});

		/*var jsonString = ""+JSON.stringify(jsonData);
		$('#myModal .modal-body').html('<pre>'+jsonString+'</pre>');
		$('#myModal').modal();*/
		$(this).button('reset');
	}
});

$('#importRecordsFromURLModal .doImportRecords').live({
	click: function(e){
		var thisForm = $('#importRecordsFromURLModal');
		$('#remoteSourceURLDisplay', thisForm).html($('form input[name="url"]').val());
		/* fire off the ajax request */
		$.ajax({
			url:'importFromURLtoDataSource', 
			type: 'POST',
			data:	{ 
				url: $('form input[name="url"]').val(), 
				data_source_id: $('#data_source_view_container').attr('data_source_id') 
			}, 
			success: function(data)
					{
						var thisForm = $('#importRecordsFromURLModal');
						thisForm.attr('loaded','true');
						var output = '';
				
						if(data.response == "success")
						{
							output = Mustache.render($('#import-screen-success-report-template').html(), data);
							$('.modal-body', thisForm).hide();
							$('div[name=resultScreen]', thisForm).html(output).fadeIn();
						}
						else
						{
								$('.modal-body', thisForm).hide();
								logErrorOnScreen(data.message, $('div[name=resultScreen]', thisForm));
								$('div[name=resultScreen]', thisForm).fadeIn();
						}
						$('.modal-footer a').toggle();

					}, 
			error: function(data)
					{
						$('.modal-body', thisForm).hide();
						logErrorOnScreen(data.responseText, $('div[name=resultScreen]', thisForm));
						$('div[name=resultScreen]', thisForm).fadeIn();
						$('.modal-footer a').toggle();
					},
			dataType: 'json'
		});
		
		$(this).button('loading');
		
		if (thisForm.attr('loaded') != 'true')
		{
			$('.modal-body', thisForm).hide();
			$('div[name=loadingScreen]', thisForm).fadeIn();
		}
		
		$('#importRecordsFromURLModal').on('hide',
			function()
			{
				load_datasource($('#data_source_view_container').attr('data_source_id'));
				thisForm.attr('loaded','false');
				$('.modal-footer a').hide();
				$('#importRecordsFromURLModal .doImportRecords').button('reset').show();
				$('.modal-body', thisForm).hide();
				$('div[name=selectionScreen]', thisForm).show();
			}
				
		);
						
		
	}
});