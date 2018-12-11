$(document).ready(function() {
	$('a').bind('click', function() {
		return false;
	});
	
	window.handlers = [];
	window.handlersForms = [];
	
	window.saveHandler = function(data) {
		if(data.type == 'email')
			var name = data.emailsend_name;
		else if(data.type == 'getpostsend')
			var name = data.getpostsend_name;
		
		data.name = name;
		
		window.handlers.push(data);
		console.log(window.handlers);
	};
	
	$.contextMenu({
		selector: 'div, span, input, button, label',
		callback: function(key, opt) {
			switch(key) {
				case 'href':
					document.location.href = opt.$trigger.attr('href');
					break;
			}
		},
		events: {
			show: function(opt) {
				var getParent = function(el) {
					if(el.parent()[0].tagName == 'FORM')
						return el;
					else if(el.parent()[0].tagName == 'BODY')
						return false;
					else
						return getParent(el.parent());
				};
				
				opt.inputs['emailsend_text'].$input.css({width: '230px', height: '180px'});
				var form = getParent(opt.$trigger);
				window.textareaValue = '';
				form.find('input, select, textarea').not('input[type=submit], input[type=hidden]').each(function() {
					var name;
					var type;
					
					if($(this).attr('name') != undefined) {
						type = 'name';
						name = $(this).attr('name');
					}
					else if($(this).attr('id') != undefined) {
						type = 'id';
						name = $(this).attr('id');
					}
					else {
						type = 'number';
						name = 'Поле' + $(this).index($(this).getParent(opt.$trigger).find('input, select, textarea').not('input[type=submit]'));
					}
					
					window.textareaValue = window.textareaValue + name + ': ' + '{{' + type + '_' + name + '}}' + "\r\n";
				});
				
				if(window.handlers.length > 0)
					opt.items.send.items.use.disabled = false;
				
				$.contextMenu.setInputValues(opt, {emailsend_title: 'Новый заказ', emailsend_text: "Информация о заказе:\r\n\r\n" + window.textareaValue + 'Время заказа: {{datetime}}', getpostsend_url: 'http://site.ru/handler.php'});
				
				opt.inputs['handlerlist'].$input.find('option').remove();
				
				for(var i = 0; i < window.handlers.length; i++)
					opt.inputs['handlerlist'].$input.append('<option value="' + window.handlers[i].name + '">' + window.handlers[i].name + '</option>');
			}
		},
		items: {
			send: {name: 'Обработчик формы', items: {
				create: {name: 'Создать', items: {
					emailsend_sub: {name: 'Email', items: {
						emailsend_name: {name: 'Название обработчика:<br/>', type: 'text'},
						space: {name: '', disabled: true},
						emailsend_email: {name: 'Email: ', type: 'text'},
						emailsend_title: {name: 'Тема: ', type: 'text'},
						emailsend_text: {name: 'Письмо:<br/>', type: 'textarea', height: '100px'},
						emailsend_submit: {name: '<button id="emailsend_save" style="">Сохранить</button>', callback: function(key, opt) {
							var data = $.contextMenu.getInputValues(opt, {type: 'email'});
							window.saveHandler(data);
						}}
					}}, getpostsend_sub: {name: 'Запрос', items: {
						getpostsend_name: {name: 'Название обработчика:<br/>', type: 'text'},
						getpostsend_type: {name: 'Запрос: ', type: 'select', options: {1: 'GET', 2: 'POST'}, selected: 1},
						getpostsend_url: {name: 'Url: ', type: 'text'},
						getpostsend_submit: {name: '<button id="emailsend_save" style="">Сохранить</button>', callback: function(key, opt) {
							var data = $.contextMenu.getInputValues(opt, {type: 'getpostsend'});
							window.saveHandler(data);
						}}
					}
					}
				}},
				use: {name: 'Применить', disabled: true, items: {
					handlerlist: {type: 'select'},
					use_submit: {name: '<button id="use_submit">Применить</button>', callback: function(key, opt) {
						var getParent = function(el) {
							if(el.parent()[0].tagName == 'FORM')
								return el.parent();
							else if(el.parent()[0].tagName == 'BODY')
								return false;
							else
								return getParent(el.parent());
						};
						
						opt.inputs['emailsend_text'].$input.css({width: '230px', height: '180px'});
						var form = getParent(opt.$trigger);
						var formPoint;
						if(form.attr('id') != undefined)
							formPoint = 'id::' + form.attr('id');
						else if(form.index('form') > 0)
							formPoint = 'formindex::' + form.index('form');
						else if(form.attr('class') != undefined)
							formPoint = 'class::' + form.attr('class');
						
						window.parent.saveFormHandler({point: formPoint, handler: $.contextMenu.getInputValues(opt, {}).handlerlist, handlers: window.handlers});
					}}
				}}
			}}
		}
	});

	
	function dump(obj) {
		var out = "";
		if(obj && typeof(obj) == "object"){
			for (var i in obj) {
				out += i + ": " + obj[i] + "\n";
			}
		} else {
			out = obj;
		}
		alert(out);
	}
});