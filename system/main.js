$(document).ready(function() {
	window.saveFormHandler = function(data) {
		$('input[name=formhandler]').val(JSON.stringify(data));
	}

	$('input[name=url]').bind('focus', function() {
		$('#tbl_charset').find('img').remove();
	});
	$('input[name=url]').bind('blur', function() {
		$('input[name=charset]').attr({disabled: 'disabled'});
		$($('#tbl_charset').find('td')[2]).append(getStatus('loading'));
		if($('#clearcache').length > 0)
			var adminpanelstring = 'sitecopy=adminpanel&password=' + JSON.parse(window.config).script_password + '&';
		else
			var adminpanelstring = '';
			
		$.ajax({url: '/?' + adminpanelstring + 'action=ajax&function=get_charset_from_url&url=' + $('input[name=url]').val(),
				method: 'get',
				success: function(data) {
							$('input[name=charset]').removeAttr('disabled');
							if(data == 'error')
							{
								$('#tbl_charset').find('img').replaceWith(getStatus('bad'));
								$('input[name=charset]').val('');
							}
							else
							{
								$('input[name=charset]').val(data);
								$('#tbl_charset').find('img').replaceWith(getStatus('success'));
							}
						}});
	});

	if($('#clearcache').length == 0) {
		$('input[name=url]').bind('keyup', function() {
			if($(this).val().match(/http:\/\//))
				$(this).val($(this).val().replace(/http:\/\//, ''));
		});
	}

	$('input[name=url]').bind('blur', function() {
		if($(this).val() != '') {
			$('#tbl_charset').show();
			$('#handlersettings').show();
			$('iframe').remove();
		}
	});
	
	$('#handlersettings').bind('click', function() {
		$('#logo, #footer').hide();
		$('body').css({overflow: 'hidden'});
		var img = '<a id="toup"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAEnQAABJ0BfDRroQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAADqSURBVFiF7ZfBCYNAEEX/hFSSDpJCPHsWy7GGnHOOXVhEKkgL8eeQCYhxVp1dWAL7YXDBmf8fKA4KSeTUIWt6LICIXETkHEVA0lUAGgAvrcbtExE+AqDW6IVIER4FkSrcDeENHwH0k+B+dm8zhDe8AVBPAOqlniQAlvEcINTrBggZLgF4IFzhIYC9EFZ4tWYQAjAgqqUs61NcAxAdbklejT5TOtOqh6jnj47GfKfXG8n73vAphIg8NbyzmlyFlUewtf57HReAAlAACkABSCFrG27RgM9Pyffskuhm8w2LnACA5CMLQAplfwfetmtC8SP7p9cAAAAASUVORK5CYII="/></a>';
		if(document.getElementById('settings-iframe') == undefined) {
			$('*').addClass('progress');
			$('<iframe/>').css({margin: '2% 0 1% 2.5%', width: '97%', height: '94.5%'}).attr({id: 'settings-iframe', src: '/?action=iframe&url=' + $('input[name=url]').val()}).appendTo('body');		
			setTimeout(function() { $.scrollTo('#settings-iframe', 1000); $('*').removeClass('progress'); }, 1000);
		}
		else
			$.scrollTo('#settings-iframe', 500);
			
		$(img).css({position: 'fixed', bottom: '0.7%', left: '0.4%'}).appendTo('body').bind('click', function() {
			$.scrollTo(0, 500);
			$(this).remove();
			$('#logo, #footer').show();
			$('body').css({overflow: 'auto'});
		});
	});
	
	$('#a_copysettings').bind('click', function() {
		$('.tbl_copysettings').toggle();
		if($('input[name=archive]').is(':checked'))
			if($('.tbl_copysettings').is(':visible'))
				$('#archive_email').show();
			else
				$('#archive_email').hide();
			
		if($('input[name=content_translate]').is(':checked'))
			if($('.tbl_copysettings').is(':visible'))
			{
				$('#content_translate_language1').show();
				$('#content_translate_language2').show();
			}
			else
			{
				$('#content_translate_language1').hide();
				$('#content_translate_language2').hide();
			}
		
		if($('input[name=use_proxy]').is(':checked'))
			if($('.tbl_copysettings').is(':visible'))
				$('#proxy_list').show();
			else
				$('#proxy_list').hide();
		
		img_example_reposition();
	});
	
	$('input[name=archive]').bind('click', function() {
		$('#archive_email').toggle();
		img_example_reposition();
	});
	
	$('input[name=content_translate]').bind('click', function() {
		$('#content_translate_language1').toggle();
		$('#content_translate_language2').toggle();
		img_example_reposition();
	});
	
	$('input[name=img_copyright]').bind('click', function() {
		$('#img_copyright_text').toggle();
		$('#img_copyright_color').toggle();
		$('#img_copyright_bg_color').toggle();
		$('#img_copyright_bg_position').toggle();
		img_example_reposition();
	});
	
	$('input[name=use_proxy]').bind('click', function() {
		$('#proxy_list').toggle();
		img_example_reposition();
	});
	
	$('input[name=replacements_standart_checkbox]').bind('click', function() { $('#replacements_standart_file').toggle(); });
	$('input[name=replacements_regular_checkbox]').bind('click', function() { $('#replacements_regular_file').toggle(); });
	
	$('input[name=multiinstall]').bind('click', function() {
		$('#multiinstall_server').toggle();
		$('#multiinstall_login').toggle();
		$('#multiinstall_password').toggle();
		$('#one_site_url').toggle();
		if($('#tbl_charset').is('visible'));
			$('#tbl_charset').hide();
		if($('input[name=charset]').val() != '' && $('#one_site_url').is(':visible'))
			$('#tbl_charset').show();
	});
	
	$('#a_imgsettings').bind('click', function() {
		$('.tbl_imgsettings').toggle();
		if($('input[name=img_copyright]').is(':checked'))
			if($('.tbl_imgsettings').is(':visible'))
			{
				$('#img_copyright_text').show();
				$('#img_copyright_color').show();
				$('#img_copyright_bg_color').show();
				$('#img_copyright_bg_position').show();
			}
			else
			{
				$('#img_copyright_text').hide();
				$('#img_copyright_color').hide();
				$('#img_copyright_bg_color').hide();
				$('#img_copyright_bg_position').hide();
			}
		
		$('#img_example').css({'left': (210 + $('#a_imgsettings').position().left + $('#a_imgsettings').width()),
							   'top': (3 + $('#a_imgsettings').position().top)});
		$('#img_example').toggle();
	});

	$('#a_replacements').bind('click', function() {
		$('.tbl_replacements').toggle();
		if($('input[name=replacements_standart_checkbox]').prop('checked'))
			//if($('.tbl_replacements').is(':visible'))
				$('#replacements_standart_file').show();
			else
				$('#replacements_standart_file').hide();
		if($('input[name=replacements_regular_checkbox]').prop('checked'))
			//if($('.tbl_replacements').is(':visible'))
				$('#replacements_regular_file').show();
			else
				$('#replacements_regular_file').hide();
	});

    $('#a_cachesettings').bind('click', function() {
        $('.tbl_cachesettings').toggle();
    });

	$('#a_multiinstall').bind('click', function() {
		$('.tbl_multiinstall').toggle();
		if($('input[name=multiinstall]').is(':checked'))
		{
			if($('.tbl_multiinstall').is(':visible'))
			{
				$('#multiinstall_server').show();
				$('#multiinstall_login').show();
				$('#multiinstall_password').show();
                $('#multiinstall_folders_and_urlss').show();
			}
			else
			{
				$('#multiinstall_server').hide();
				$('#multiinstall_login').hide();
				$('#multiinstall_password').hide();
                $('#multiinstall_folders_and_urls').hide();
			}
		}
	});
	
	function img_example_reposition() {
		$('#img_example').animate({'left': (210 + $('#a_imgsettings').position().left + $('#a_imgsettings').width()),
							   'top': (3 + $('#a_imgsettings').position().top)});
	}
	
	function getStatus(type)
	{
		switch(type) {
			case 'loading': return '<img src="data:image/gif;base64,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" />'; break;
			case 'success': return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACEklEQVR4Xr3QXUhTcRgG8Oec8z/HbZSrttmSwrnEFCEaqZtQIExZSpShNUQyisiyKMlIKNoS6QOhpmWFLBMiLBpCaYu8SL2TPrCoMJaUg2gZsRJHuq1tb0MY7CKi3fiDF96b57l4sPiU0MdPlwjwSM3KnbYyV2VLiQuABilRI7ehZ8f7mch3+jI3RbVdlglJhTw+EZC0yIcGOfgLuY4ZW5zW0RM19rxp/2f8DoaxvbwiX7t16ZWFgiXZbHPjjapnB6+Zx+RrWCGSaAzCltO91qfVpj2rpn964Q/6MPSpB+f72+Abn/8KlUHadmywIvR48hYNfLhOzU/MvzI2COWIW1um2H35ZV1saKqbBjxXadDTRZee19HGiyoSC8QHC5Oub1K+qXfnUOtINTnHm6lvwkZnRswhiz3jXucrK7k8drrz7iT1vj1K516UkrFdTUzPbgIQEMeJuWJ3cYPywLoCCSzGsFyxAsvkKogyQIilIRgJYC7yAyQEMTocwFiH/0J0OnoqsQ8HQBSyBEfpcfXhwmIZguEIJMZB4AGe58AJQLpMiaGBEIbbPzZhJtaJJFziYauZrepsdmuRScJ8eBYsHuYZoFbo4e6fCT1se70Ps+jDv6Rl8of23jZEnd5N5JjMovu+Gqp1lPiRDgv+lyyT33Xkring/tZI9R0mL+QwIlUKrVBZtF/5CCIKkKLkbXRYDH8AqnK32MRsAT8AAAAASUVORK5CYII=" />'; break;
			case 'bad': return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACu0lEQVR4XoWQX0ibZxjFfwnJp2lswD8bG20KDSbMKkQJbUhXLQZdtUuZ3YzTMpHawabdxbAXE0svBsKsnXfdRejWK8fMhrbkZpnZoNiBQp2jsl7MrNRRus0NXSv9CzGnn1FEacd+cC5envec5/CwjpGqro5PhMOjQB7/QRl4f29tnT7jdvewie3jdXVjSqW0qu8OHrwMOJ5nnu/qSmtqShoe1kBpaS9gpQLqs9GoNDYmxePSyIiSBw4kAMcWc0dHWpcurf0x9VsgcB94kZeg8XPQcmOjdOGCFIvlNB4MJgDDB7vnW1rSunhxbXb+vO74/ToNy8DLADuOwmwuJByWBgels2elgQFdqaz8fq6h4RcNDeXe6u/X7T17dA4UgmHAbgGwQ/mbED8C5W8EgxSEwyCBJTcGCTIZ/kgkGJ2b4xsYuwongLuWzXdqh68awR+pqmL7/v0gbQT8mUySuHUrZ/4BOoF7ABa24h602ca7Q6FXnD4fwEaLpdlZ+mZmkrFM5ijwmHWsbOItw6joDIWKnaWlsLKypmw2pyKvl4+8Xk/Ibi8DeCbg/fz8jpGSksvFy8svkE7D0hIL16+zdOMGLCzAzZvsdjh833o8qci2ba+xmV6H40Pt2iWVl0t790qHD+uvYFApi0VXDEOLdXVSa6t06JBUU6Mnfv/D4y5XGwA9eXkn5XavmUMhqaVF/9TX60e7XRGItcPX1xwOLTY3S93dUlOTVFsrBQLZaEFBM5/YbKNZj0cyN6qtTYvm9knTXA8DgBVwvg1fzOTna/HYMenUKSka1cq+fTppGJ/xLsTmzeGDhgbdjUQ0abVmXoVetmK8Dud+MoP/NUPum/rVbHUEPqYAyj6Fn++ZDaaczkdV8B7Px1INvdMuV+bvykr1wQTgZh1fT1FRvMxqfYf/IWCzdX1QWPglsBPgKb5aRnCvIQYqAAAAAElFTkSuQmCC" />'; break;
			default: alert('Sorry, i don`t know that u want!');
		}
	}
	$('.tbl_copysettings').show();

	function exampleAjax() {
		var url = 'index.php?sitecopy=sc&image_example=1&mirror=';
		if($('input[name=img_mirror]').prop('checked'))
			url = url + 'on';
		else
			url = url + 'off';

		$('#img_example img').css({opacity:0.5});
		$('#img_example').html($('#img_example').html() + getImg('loading'));
		$.ajax({url: url,
			type: "POST",
			async: true,
			data: {"img_copyright_text": $('input[name=img_copyright_text]').val(),
					"img_zoom": $('input[name=img_zoom]').val(),
					"img_copyright_bg_color": $('input[name=img_copyright_bg_color]').val(),
					"img_copyright_bg_position": $('input[name=img_copyright_bg_position]').val(),
					"img_copyright_color": $('input[name=img_copyright_color]').val()
			},
			success: function(data) {
				$('#img_example .pro-img').remove();
				$('#img_example img').css({opacity:1});
				$('#img_example img').attr({src: data});
			}
		});
	}

	$('input[name=img_mirror]').bind('click', exampleAjax);
	$('input[name=img_copyright_text]').bind('change', exampleAjax);
	$('input[name=img_copyright_bg_color]').bind('change', exampleAjax);
	$('input[name=img_copyright_color]').bind('change', exampleAjax);
	$('input[name=img_copyright_bg_position]').bind('change', exampleAjax);
	$('input[name=img_tilt_max]').bind('change', exampleAjax);
	$('input[name=img_zoom]').bind('change', exampleAjax);

	function getImg(type) {
		var loading = ' <img class="pro-img" src="data:image/gif;base64,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" />';
		var good = ' <img class="pro-img" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAMFSURBVHjaYvz//z8DJQAgAINxcAQgCANA8MgQlRkefrUDLZM6LBQSAd3XxlxOZofYNtQTWhMWOqYVxHjVsdWY0gj/x+Iw7NqP/KByfwKIiSTrQI79/TtBXVlqV6S1hZUAK98/gAAi2gCgV0X/MTHMdrfQm1/r6Shz8drzfw/uvEoECCCW/wz/FYHB8A2o5iUOnQz//vwz4xbjnJ/soqUVoKHM0LH7PMOx0w8yGbmZNgEEEIuQCO/VX7//3fz8+I8PGwPDUxS9//6z/vj7J1VOTbCnwseY00ZcgKF853mGXQcf1DOxs8xiYPzHABBATHzM3J99HBQNuAXZVv7+808IrJMRGE5//2v85WBY5uaoNHVqmAenlbgkQ+Oeawy79j7vZ/vP0sTABIk9gABienbn+0wJBjGG7EAra0YuhnX//jEw/vn9z5VXnGVXarBBSIOrO4M4Dw9D78FLDNu2v1zF/pu5nAEp5AACAEEAvv8DBjAZ5u70+2RxSUoEBw3+H8fX4mUiKg1hdlc6FEoiMwD4/vMAn8nQAK6t6bgA+v1dAAf89/wE+gD9BvkA/wf8AAIAQQC+/wMQMCHw7e73HRUcCDNXMDwBCBn3DlM2LgUqDyMA7vzpALHU0wCxquzRAfIBWQUFAuwD/QQABf0GAAb7CQAG+QoAAgBBAL7/AxAvIv/99AX78gHvWy8nFwEwFiQAA/8DAO/96gC/3dYArqXx4P/tAU0GBwHgBfwGAAb8CAAI+QwACPYPAAn0EAACiFlIQ5uB9Q/H4y+f/1nxq35V4mFnYfj0/xXD6Sf3GLYu/f7m91OWACaO31f+M/+DeJoRGPEgzPQPjAECiInjFzsD5z+2n4zP+LaePv2e4TnjHYaLr+4z7Fz668uv21wxzMwMp/ElMIAAYmL+y84Awhz/2Be9PsP98MTt5wxHVzH/+n5ZIBuoeSehFAoQQMwSakbAKGVmYGJk/M70jUPszW0eix/XeesYmP9M+8/2h+Ef82+G/yzADAT2AiOGAQABxEhpdgYIMABQrSEzlY7wIwAAAABJRU5ErkJggg==" />';
		var bad = ' <img class="pro-img" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACtklEQVR4Xn1TS0sbURQ+d2aSZhI3btwJhYgogahtIT4Sp3k0hlIDokVREVwZFHEhqCj+ARH6E4RSY1ZduSi0aMHqtj4SFR+YqBUfMfXRydg26em5lilDKB24nJzJ9333nPOdAUS8P28A6mYB4hQ9PDccI+Zx9A/Gp2MEoGeWMUWQpA/lwaCD4jvKG6HgIYyLMfbxYU2NQ7Ra5yn3cQBXfTYny+r+0BCeTU3hXiSCUZNJpfeK4eaGWUH4Fm9qwsOeHkw0N2NMljVeCVDZG2tdXXg+PY1Hvb14NjmJ+1zEbOYiXi4UlSR1p7UVvxBuz+HAVGcnLlVWIm+HEaBGsliWXIODNqvFAvlkEkylpaBmMrAyM6MxRHC1t8tWSYK79XUw2e2QSadhfXFRQ0SvXmI9byM1MoIXExN4Sjdcjo7i8cAAHvf3Y7qvD088Hjzt7sYdKp/61/RBGl3wk0j2mEQux8fxoqMDvw4PY4ZEzoNBTBP5oK0N50TxjrDNOq/QpnDMZsue0e1XY2OYaWnBS5cL06EQntAMYiYTJ3cZOUKBWwzyeQYcoKqQ394Gdn0NAuUi/1MQflEoMhIEg88hyWyONUQiFvHqCrT5eRDKygDo/NzdBUaCtYGALEnSK8K+NApwslcQxbdVHo+Fra2BSmSxogJuEeGWMWD0+0ciAeZcDh4piiwIwmviBDmXcQeotPdVbre1iMC5zU2Qq6vhBgA+LyzcMcI8CQQe2BDhO9koOZ1wk83C6vJyFhH9fJGSn8rL8YDs2SouxpTXi3FFQdq8LIk/pfM8Korals+HR34/7paU4CENd8lu54uUuJ88gbUNtxuTJLJaX6+TPQZ3Alxko7ERU+EwxilyDrf+r320rhpfT+pN5bv/jy9R4cIrTidyLOWhwkUKUzv7FGv/8znXUdlJii90zG+f6cW6bvDIXgAAAABJRU5ErkJggg==" />';

		if(type == 'good' || type == 'bad' || type == 'loading')
			return eval(type);
	}


	$('#img_a').bind('click', function() {
		$('#img_table').toggle();
		$('#img_example').toggle();
	});

    $('input[name=multiinstall_password]').bind('blur', function() {
        $('#multiinstall_folders_and_urls').show();
        $('textarea[name=multiinstall_site_folders]').val('Загрузка..');
        $('textarea[name=multiinstall_site_folders]').attr('disabled', 'disabled');

        var url = 'index.php?action=ajax&function=get_ftp_folders&server=' + $('input[name=multiinstall_server]').val() + '&login=' + $('input[name=multiinstall_login]').val() + '&password=' + $('input[name=multiinstall_password]').val();

        $.ajax({
            url: url,
            method: 'get',
            success: function(response) {
                $('textarea[name=multiinstall_site_folders]').val(response);
                $('textarea[name=multiinstall_site_folders]').removeAttr('disabled');
            }
        });
    });

});