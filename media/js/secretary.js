/*
* @package     com_secretary
* @copyright   Copyright (C) Fjodor Schäfer, SCHEFA.COM - All Rights Reserved.
*
**********************************************************************
* 
* These file is proprietary of SCHEFA.COM, copyrighted and cannot be redistributed
* in any form without prior permission from SCHEFA.COM
*			
**********************************************************************
*
* @license     SCHEFA.COM Proprietary Use License
*
*/

jQuery = jQuery.noConflict();

if(typeof(secretary) == 'undefined') {
	var secretary = {};
}
if(typeof(secretary.jQuery) == 'undefined') {
	secretary.jQuery = jQuery.noConflict();
}
 
var chosenSubject = {};

secretary.jQuery( document ).ready(function( $ ) {


	/******************************************************************************
	**********	C l a s s
	*******************************************************************************/
	
	if(typeof(Secretary) === 'undefined')
		Secretary = { };
	
	Secretary.Search = {
			
		drawBlockInput : function(container, title) {
			$(container).parent().children('div.input-blocked').remove();
			$(container).parent().prepend('<div class="input-blocked">'+ title + Secretary.Search.removeInput+'</div>');
			$(container).hide();
		},
		
		drawBudgetContainer : function( item, container ) {
			
			if(typeof(item.id) == 'undefined')
				return;
			
			var container = container || "input.search-documents";
			Secretary.Search.drawBlockInput(container, item.value);
			$("#jform_document_id").val(item.id);
				
			$('.budget').empty();
			$('.budget').append(
				'<div class="budget-total">'+ item.total + ' ' + item.currency + '</div>' +
				'<div class="budget-category"><a rel="{size: {x: 800, y: 500}, handler:\'iframe\'}" href="index.php?option=com_secretary&view=document&id='+item.id+'&tmpl=component&layout=preview" class="modal" target="_blank">'+ item.category + ' / '+ item.created + '</a></div>'
			)
		},
		
		extractLast : function ( term ) {
			split = function ( val ) {
				return val.split( /;\s*/ );
			};
			return split( term ).pop();
		},
		
		removeInput : '<span class="removeInput">x</span>',
	}

	Secretary.Fields = function( fields ) {
			
		function addField(id, hard, title, box, description){
			var counter = $('#field-add').attr('counter'); 
			var html = $('.field-item:first').html();
			html = html.replace(/##values##/g, stripslashes(box));
			html = html.replace(/##counter##/g, counter);
			html = html.replace(/##id##/g, id);
			html = html.replace(/##hard##/g, hard);
			html = html.replace(/##title##/g, title);
			html = html.replace(/##description##/g, description);
			var parent = $('<div class="field-item '+hard+'">' + html + '</div>');
			if(description.length < 1) parent.find('.tooltip-toggle').remove();
			parent.appendTo('.fields-items').show();
			$('#field-add').attr('counter', parseInt(counter) + 1);
		};

		$('#field-add').click(function(){
			var id = $('#getfields').val();
			var ext = $('#getfields').data('ext');
			var json = getFieldObject(id,ext); 
			if(json) addField(json.id, json.hard, htmlEntities(json.title), json.box,json.description );
			return false;						
		});
		
		function printFields (fields) {
			var ext = $('#getfields').data('ext');
			for(var i in fields){
				if(fields.hasOwnProperty(i) ){
					for(var key in fields[i]){
						if(typeof(fields[i][key][0]) !== 'undefined')
						{
							var json = getFieldObject(fields[i][key][0], ext, fields[i][key][2] );
							if(json !== null){
								addField(json.id, json.hard, htmlEntities(fields[i][key][1]),cleanJSONbreaks(json.box),json.description);
							}
						}
					}
				} 
			}
		}
		
		function getFieldObject(id,extension,standard) {
			var json = null;

			var input = {};
			input.id = id;
			input.extension = extension;
			if(typeof(standard) !== 'undefined') {  input.standard = encodeURIComponent( standard.replace(/#/g, "") ); }

			$.ajax({
				  type: "POST",
				  async: false,
				  url: "index.php?option=com_secretary&task=ajax.getField",
				  dataType: "json",
				  data: input,
				  success: function(data){
						json = data;
					},
				});

			return json;
		}

		function htmlEntities(str) {
			return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
		}
		
		function stripslashes(str) {
		  return (str + '')
			.replace(/\\(.?)/g, function(s, n1) {
			  switch (n1) {
				case '\\':
				  return '\\';
				case '0':
				  return '\u0000';
				case '':
				  return '';
				default:
				  return n1;
			  }
			});
		}

		function cleanJSONbreaks(str){
			return str.replace(/BREAK/g, "\n");
		}
		
		printFields(fields);
		
		$('.field-remove').live('click', function(){
			$(this).parents('.field-item').remove();
			return false;			
		});
		
	};

	Secretary.Ajax = {
		
		call : function(container, task, id)
		{
			$(container).addClass('ui-autocomplete-loading');
			$.getJSON(
				"index.php?option=com_secretary&task="+task+"&id=" + id ,
				function(data){
					$(container).removeClass('ui-autocomplete-loading');
					$(container).replaceWith('<div class="btn btn-email-disable">'+ data.msg+'</div>');
				}
			);
		},
		
	};

	Secretary.submitbutton = function(task) {
		document.adminForm.task.value = task;
		document.adminForm.submit();
	};
	
	Secretary.Accounting = {
		
		total : function() { return $('#acc_total_amount_total').val(); },
		subtotal : function() { return $('#acc_total_amount_subtotal').val(); },
		taxtotal : function() { return $('#acc_total_amount_tax').val(); },
		clear : function() { $('#secretary-accounting input').val(''); },
		
		add : function(type, account, accountid, sum) {
			
			var counter = $('.add-' + type).attr('counter'); 
			counter =  parseInt(counter);
			
			var html = $('.secretary-acc-row:first').html();
			html = html.replace(/##counter##/g, counter);
			html = html.replace(/##type##/g, type);
			html = html.replace(/##account##/g, account);	
			html = html.replace(/##accountid##/g, accountid);	
			html = html.replace(/##sum##/g, parseFloat(sum).toFixed(2) );
			
			$('<div class="secretary-acc-row clearfix">' + html + '</div>').appendTo('.secretary-acc-rowlist-'+type).show();
			$('.add-' + type).attr('counter', parseInt(counter) + 1);
			
			return false;
		},
		
		run : function() {
			// accounting.clear();
	
			var total	= Secretary.Accounting.total();
			var soll	= 0.0;
			var haben	= 0.0;
			
			if(typeof(accJSON) === 'undefined')	{
				if($('.secretary-acc-rowlist-s').is(':empty')) {
					Secretary.Accounting.add('s','','','');
				}
				if($('.secretary-acc-rowlist-h').is(':empty')) {
					Secretary.Accounting.add('h','','','');
				}
				return;
			}
			
			for(var accType in accJSON){
				if(accJSON.hasOwnProperty(accType)){
					for(var booking in accJSON[accType]){
						if(accJSON[accType].hasOwnProperty(booking)){
							Secretary.Accounting.add(accType,accJSON[accType][booking][0],accJSON[accType][booking][1],accJSON[accType][booking][2]);
						}
					}
				}
			}
		
		},
		
		check : function () {
			
			/*
			return $('.secretary-acc-total').live('change',function(){
				var total = parseFloat($('.secretary-acc-sum').text()).toFixed(2);
				var soll = total;
				$('.acc_s_sum').each(function() {
					soll -= Number($(this).val()).toFixed(2);
				});
				var haben = total;
				$('.acc_h_sum').each(function() {
					haben -= Number($(this).val()).toFixed(2);
				});
				if(soll !== 0 || haben !== 0) {
					alert('Warning!');
					return false;
				}
			});
			*/
		}

	};

	$('#pdf_select').change(function() {
		var value = $(this).val();
		switch(value) {
			case 'mpdf': $('.secretary-desc').children().hide(); $('.secretary-desc #mpdf').show(); break;
			case 'dompdf': $('.secretary-desc').children().hide(); $('.secretary-desc #dompdf').show(); break;
			default : $('.secretary-desc').children().hide(); break;
		}
	});
	
	/******************************************************************************
	**********	A c c o u n t i n g
	*******************************************************************************/
	
	Secretary.Accounting.check();
	
	$('.btn-buchen').bind('click', function (event) {
		event.preventDefault();
		
		if(Secretary.Accounting.check() === false)
			return;
		
		$(this).addClass('ui-autocomplete-loading');
		var form = $(this).parents('form:first');
		var formTask = $('#formtask');
		formTask.val('ajax.buchen');
		var container = $(this);
		var id		= $(this).data('id');
		$.ajax({
			type: 'POST',
			url:  "index.php?option=com_secretary&task=ajax.buchen&id=" + id,
			data: form.serialize(),
			success: function (response) {
				res = JSON.parse(response);
				if(res[0] == 200) {
					$('#secretary-document-accounting').empty();
					$('#secretary-document-accounting').text(res[1]);
				} else {
					$('<div class="alert alert-warning fullwidth">'+res[1]+'</div>').appendTo('#secretary-document-accounting');
				}
				container.removeClass('ui-autocomplete-loading');
				formTask.val('');
            }
		});
	});
	
	$('.secretary-acc-add').on('click', function() {
		var type = $(this).data('type');
		var rest = parseFloat($('.secretary-acc-sum').text());
		
		$('.acc_'+type+'_sum').each(function() {
			rest -= Number($(this).val());
		});
		
		Secretary.Accounting.add(type, '', '', rest);
	});
	
	$('.acc-row-remove').live('click', function() {
		$(this).parents('.secretary-acc-row').remove();
	});
	
	Secretary.Accounting.run();
	
	$("input.search-accounts, input.search-accounts_system").live('focus', function() {
		$(this).autocomplete({
			source: 'index.php?option=com_secretary&task=ajax.searchAccounts', 
			minLength:2,
			open: function(event, ui) {
				$(".ui-autocomplete").css("z-index", 1000);
			},
			select: function( event, ui ) {
				$(this).val(  ui.item.nr +" "+ ui.item.title );
				$(this).next().val( ui.item.id );
				return false;
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( '<a><span class="ui-menuitem-value">'+ item.nr + ' ' + item.title + '</span></a>' )
			.appendTo( ul );
		};
	});
	 
	/******************************************************************************
	**********	S e a r c h
	*******************************************************************************/
	
	$( "input.search-documents" ).live('focus', function() {
		$(this).autocomplete({
			source: 'index.php?option=com_secretary&task=ajax.searchDocuments', 
			minLength:1,
			open: function(event, ui) { $(".ui-autocomplete").css("z-index", 1000); },
			select: function( event, ui ) {
				var parent = $(this).parent();
				if(parent.hasClass('controls')) {
					Secretary.Search.drawBudgetContainer( ui.item, this );
				} else if(parent.hasClass('table-item-col-2')) {
					var row = parent.parent();
					row.find('.table-item-nr').val( ui.item.nr );
					row.find('.table-item-created').val( ui.item.created );
					row.find('.table-item-deadline').val( ui.item.deadline );
					row.find('.table-item-price').html( Number( ui.item.subtotal ) );
					row.find('.table-item-taxrate').html( Number( ui.item.tax ) );
					row.find('.table-item-col-5 span').html( Number( ui.item.total ) );
					row.find('input.table-item-total').val( Number( ui.item.total ) );

					row.find('.add-subject-as-contact span').html( ui.item.subject.fullname );
					row.find('input.table-item-subjectid').val( Number( ui.item.subjectid ) );
					secretary.entry.calculate.total();
					$(this).next().val(ui.item.id);
					chosenSubject = ui.item.subject;

				}
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( '<a><span class="ui-menuitem-value">'+ item.value + '</span><br><span class="ui-menuitem-sub">'+ item.subject.fullname +'<br>'+ item.total + ' '+ item.currency  + '</span></a>' )
			.appendTo( ul );
		};
	});
	
	if(typeof(budget) !== 'undefined')
		Secretary.Search.drawBudgetContainer(budget,"input.search-documents");
	
	if ( $( ".search-locations" ).length)
	{
		var ext = $("input.search-locations").data("extension");
		$( "input.search-locations" ).autocomplete({
			source: 'index.php?option=com_secretary&task=ajax.searchLocations&extension='+ext, 
			minLength:2,
			open: function(event, ui) {
				$(".ui-autocomplete").css("z-index", 1000);
			},
			select: function( event, ui ) {
				Secretary.Search.drawBlockInput(this, ui.item.title);
				$("#jform_location_id").val(ui.item.id);
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( '<a><span class="ui-menuitem-value">'+ item.title + '</span><br><span class="ui-menuitem-sub">'+ item.street + ', '+ item.zip + ' ' + item.location + '</span></a>' )
			.appendTo( ul );
		};
	};
	
	
	if ( $( "input.search-subject-zip" ).length)
	{
		$( "input.search-subject-zip" ).autocomplete({
				source: 'index.php?option=com_secretary&task=ajax.searchSubjectLocation&type=zip', 
				minLength:2,
				open: function(event, ui) {
					$(".ui-autocomplete").css("z-index", 1000);
				},
				select: function( event, ui ) {
					$('#jform_subject_location').val(ui.item.location);
				}
			})
			.autocomplete( "instance" )._renderItem = function( ul, item ) {
				return $("<li>").append( '<a><span class="ui-menuitem-value">'+ item.zip +' '+ item.location + '</span></a>' ).appendTo(ul);
			};
	}
	if ( $( "input.search-subject-location" ).length)
	{
		$( "input.search-subject-location" ).autocomplete({
				source: 'index.php?option=com_secretary&task=ajax.searchSubjectLocation&type=location', 
				minLength:2,
				open: function(event, ui) {
					$(".ui-autocomplete").css("z-index", 1000);
				},
				select: function( event, ui ) {
					$('#jform_subject_zip').val(ui.item.zip);
				}
			})
			.autocomplete( "instance" )._renderItem = function( ul, item ) {
				return $("<li>").append( '<a><span class="ui-menuitem-value">'+ item.zip +' '+ item.location + '</span></a>' ).appendTo(ul);
			};
	}
	
	if ( $( ".search-features" ).length)
	{
		
		var theList = '.posts.multiple-input-selection';
		var source = $(theList).data("source");
		var extension = $(theList).data("extension");
		var counter = $(theList).data("counter");
		
		Secretary.Features = {
			clear : function() { $(theList).focusout(function(){ $('.search-features').val(''); }); } ,
			input : function(name,type,key,counter) {
						return '<input type="'+type+'" name="jform[features]['+counter+']['+name+']" value="'+ key +'">';
					},
			textarea : function(name,key,counter) {
						return '<textarea name="jform[features]['+counter+']['+name+']" class="fullwidth" placeholder="...">'+ key +'</textarea>';
					},
			results : function() {
				var counter = 0;
				for (var key in featuresList) {
				   if (featuresList.hasOwnProperty(key)) {
					   var subject = featuresList[key];
						var li = '<div class="added-post clearfix">' + 
							'<div class="added-post-left">'+ 
							'<div class="added-post-title">'+ subject.firstname + ' ' + subject.lastname + 
							Secretary.Features.input('id','hidden',subject.id,counter) +  '</div>' ;
							delete subject.id; delete subject.firstname; delete subject.lastname;
						li += Secretary.Features.textarea('note', subject.note, counter);
							delete subject.note;
								
							for (var attributes in subject) { 
								li += Secretary.Features.input( attributes ,'hidden', subject[attributes] ,counter);
							}
							
						li += '</div>' + Secretary.Search.removeInput + '</div>';
							
						$(li).prependTo('div.posts');
						counter++;
				   }
				}
			}
		};
		
		$( ".search-features" ).autocomplete({
			source: function( request, response ) {
				$.getJSON( 'index.php?option=com_secretary&task=ajax.searchSubjects&source='+ source, {
				term: Secretary.Search.extractLast( request.term )
				}, response );
			},
			focus: function() { return false; },
			search: function() {
				var term = Secretary.Search.extractLast( this.value );
				if ( term.length < 2 ) {
					return false;
				}
			},
			select: function( event, ui ) {
				var li = '<div class="added-post clearfix">' + 
							'<div class="added-post-left">'+ 
								'<div class="added-post-title">'+ ui.item.value + 
								Secretary.Features.input('id','hidden', ui.item.id, counter) + '</div>' + 
								Secretary.Features.textarea('note', '', counter) + 
							'</div>' + 
							Secretary.Search.removeInput + 
						'</div>';
				$(li).prependTo('div.posts');
				$(this).val('');
				counter++;
				return false;
			}
		})
		.autocomplete( "instance" )._renderItem = function( ul, item ) {
			return $( "<li>" )
			.append( '<a><span class="ui-menuitem-value">'+ item.value + '</span><br><span class="ui-menuitem-sub">'+ item.street + ', '+ item.zip + ' ' + item.location + '</span></a>' )
			.appendTo( ul );
		};
		
		Secretary.Features.clear();
		
		if( typeof(featuresList) !== 'undefined' ) { 
			Secretary.Features.results();
		}
		
	}
	
	$('.removeInput').live('click',function() {
		var container = $(this).parent();
		var control = container.parent();
		container.remove();
		if( !control.find('input.search-block-input').is(':visible') ) {
			control.find('input.search-block-input').show();
			control.find('div.budget').empty();
			control.children('input').val('');
		}
	});
				
	$('.btn-submittask').live('click', function () {
		var form = $(this).parents('form:first');
		var formTask = form.children('#form-task');
		var value = $(this).data("value");
		formTask.val(value);
		form.submit();
	});
	
	
	/******************************************************************************
	**********	S i d e b a r
	*******************************************************************************/
	
	$(".secretary-toggle-sidebar").click(function(){
		$('.secretary-container').toggleClass('active');
	});

	$("#show-sidebar").hide();
		
	$("#document-repetition-check").live('change', function() {
		$(this).next('.repetition-container').slideToggle().toggleClass('out');
	});
	
	$(".fa.pull-right").click(function() {
		var target = $(this).data('target');
		$(target).slideToggle().toggleClass('out');
		$(this).toggleClass('fa-angle-left fa-angle-down');
	});
	 
	$('#sidebar-angle').click(function() {
		
		$(this).toggleClass('show-sidebar hide-sidebar');
		$('.nav-item-text').toggleClass('hide-sidebar-text');
		
		var children = $(this).children();
		if(children.hasClass('.fa-angle-left'))
			children.toggleClass('fa-angle-left fa-angle-right');
		else 
			children.toggleClass('fa-angle-right fa-angle-left');
		
		var angle = $('.secretary-sidebar-container').find('.fa.pull-right');
		if(angle.hasClass('.fa-angle-down'))
			angle.toggleClass('fa-angle-down fa-angle-right');
		else 
			angle.toggleClass('fa-angle-left fa-angle-right');
		
		var down = children.hasClass('fa-angle-right');
		$('.secretary-sidebar-container').toggleClass('hidden-sidebar');
		$.ajax({ 
			url : 'index.php?option=com_secretary&task=ajax.toggleSidebar&v=' + (+ down)
		})
	});
	
	$( "input[type=checkbox]" ).on( "click", function() {
		var n = $( "input:checked" ).length;
		if(n > 0) {
			$('.secretary-container button.hidden-toolbar-btn').fadeIn();
		} else {
			$('.secretary-container button.hidden-toolbar-btn').fadeOut();
		}
	});

	
	/******************************************************************************
	**********	M e s s a g e s
	*******************************************************************************/
	
	$('.message-talk-item-form-bottom > button[type="submit"]').click(function(){
	});
	
	$('.message-talk-item-top select').on('change',function(){
		var id		= $(this).data('id');
		var value	= $(this).val();
		$.ajax({ 
			url : 'index.php?option=com_secretary&task=message.changeStatus&id=' + id + '&value='+value
		}).done(function(data) {
			alert(data);
		});
	});
	 
	$('#secretary-form-message').keyup(function() {
		var postLength = $(this).val().length;
		$('.counter').text(postLength);
		if(postLength == 0) {
			$('.btn.btn-success').addClass('disabled');
		}
		else {
			$('.btn.btn-success').removeClass('disabled');
		}
	});

	/*******************************************************************************/

	/* Configuration - Access */
	$('#settings_access .input-small').change(function(){
		console.clear();
		var selectBox = $(this);
		var input = {};
		input.section = $(this).data('section');
		input.action = $(this).data('action');
		input.group = $(this).data('group');
		input.value = $(this).val();
		var loader = 'background: url(../media/system/images/modal/spinner.gif);display:inline-block;width:16px;height:16px;';
		selectBox.next().attr('style',loader);
		selectBox.next().removeClass('icon-save');
		$.ajax({
            type: "POST", 
            url: "index.php?option=com_secretary&task=ajax.updatePermission",
            dataType: "json",
            data: input
		}).done(function(){
			selectBox.next().removeAttr('style');
			selectBox.next().addClass('icon-save');
		});
	});
	
	$('select#jform_catid').change(function(){
		var v = $(this).val();
		$('input#catid').val(v);
	});
	
    $('.custom-columns-btn').click(function(){
        $(this).toggleClass('active');
		$('.chk_items_container').slideToggle();
    });

	$('.secretary-sort .move-up').click(function(){
		var parent = $(this).closest('.secretary-row-inner').parent();
		if(parent.hasClass('secretary-sort-row')) {
			var before = parent.prev();
			parent.insertBefore(before);
		}
	});
	$('.secretary-sort .move-down').click(function(){
		var parent = $(this).closest('.secretary-row-inner').parent();
		if(parent.hasClass('secretary-sort-row')) {
			var next = parent.next();
			parent.insertAfter(next);
		}
	});
});	