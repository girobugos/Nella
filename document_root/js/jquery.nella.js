/**
 * Nella
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the "New BSD license" that is bundled
 * with this package in the file nella.txt.
 *
 * For more information please see http://nellacms.com
 *
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @copyright  Copyright (c) 2009 Jan Marek
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @license    http://nellacms.com/license  New BSD license
 * @link       http://nellacms.com
 * @category   Nella
 * @package    Nella
 */
 
/** global functions  */
jQuery.extend({
	nella: {
		updateSnippet: function(id, html) {
			jQuery("#" + id).fadeTo("fast", 0.3, function () {
				jQuery(this).html(html).fadeTo("fast", 1);
			});
		},

		success: function(payload) {
			// redirect
			if(payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}
			
			// snippets
			if(payload.snippets) {
				for(var i in payload.snippets) {
					jQuery.nella.updateSnippet(i, payload.snippets[i]);
				}
				jQuery('.popup-overlay').animate({"opacity": 0}, 'slow', function() {jQuery(this).remove();});
			}
		},
		
		renderPopupDialog: function(url) {
			alert(url);
			jQuery.getJSON(url, function(data) {
				jQuery('<div />').addClass("popup-window-dialog").appendTo(document.body);
				jQuery('<p>'+data.question+'</p>').appendTo('.popup-window-dialog');
				jQuery('<p> | </p>').prepend(
					jQuery('<a />').attr('href', data.yesLink).text(data.yesText)
				).append(
					jQuery('<a />').attr('href', "#").bind('click', function() {
						jQuery('.popup-window-dialog').animate({"opacity": 0}, 'slow', function() {jQuery(this).remove();});
						jQuery('.popup-overlay').animate({"opacity": 0}, 'slow', function() {jQuery(this).remove();});
						return false;
					}).text(data.noText)
				).appendTo('.popup-window-dialog');
			});
			return false;
		},
	}
});

/** plugins  */
jQuery.fn.extend({
	nellaSubmit: function (callback) {
		var form;
		var sendValues = {};

		// submit button
		if (this.is(":submit")) {
			form = this.parents("form");
			sendValues[this.attr("name")] = this.val() || "";

		// form
		} else if (this.is("form")) {
			form = this;

		// invalid element, do nothing
		} else {
			return null;
		}

		// validation
		if (form.get(0).onsubmit && !form.get(0).onsubmit()) return null;

		// get values
		var values = form.serializeArray();

		for (var i = 0; i < values.length; i++) {
			var name = values[i].name;

			// multi
			if (name in sendValues) {
				var val = sendValues[name];

				if (!(val instanceof Array)) {
					val = [val];
				}

				val.push(values[i].value);
				sendValues[name] = val;
			} else {
				sendValues[name] = values[i].value;
			}
		}

		// send ajax request
		var ajaxOptions = {
			url: form.attr("action"),
			data: sendValues,
			type: form.attr("method") || "get"
		};

		if (callback) {
			ajaxOptions.success = callback;
		}

		return jQuery.ajax(ajaxOptions);
	}
});
	
/** setup */
jQuery.ajaxSetup({
	success: jQuery.nella.success,
	dataType: "json"
});
jQuery(document).ready(function() {
	//overlay + spinner
	jQuery('<div />').addClass("ajax-spinner").hide().ajaxStart(function() {
		jQuery(this).show();
		jQuery('<div />').addClass("popup-overlay").css({
			width: jQuery(window).width(),
			height: jQuery(document).height(),
			opacity: 0.6,
		}).appendTo(document.body);
	}).ajaxStop(function () {
		jQuery(this).hide();
	}).appendTo(document.body);
	
	//ajax
	jQuery(".ajax").live('click', function() {
		return jQuery.getJSON(this.href);
	});
	//ajax dialog
	jQuery(".ajax-dialog").live('click', function() {
		return jQuery.nella.renderPopupDialog(jQuery(this).attr('href'))
	});
	//hide flash message
	jQuery(".flash-message").livequery(function() {
		var el = jQuery(this);
		setTimeout(function () {
			el.animate({"opacity": 0}, 'slow', function() {jQuery(this).remove();});
		}, 5000);
	});
	
	// datagrind links
	jQuery("table.datagrid a.datagrid-ajax").live("click", function () {
		jQuery.get(this.href);
		return false;
	});
	// datagrid form buttons
	jQuery("form.datagrid :submit").live("click", function () {
		 jQuery(this).nellaSubmit();
		return false;
	});
	// datagrid form submit
	jQuery("form.datagrid").livequery("submit", function () {
		jQuery(this).nellaSubmit();
		return false;
	});
	// datagrid filter ajax press <ENTER>
	jQuery("form.datagrid table.datagrid tr.filters input[type=text]").livequery("keypress", function (e) {
		if(e.keyCode == 13) {
			jQuery(this).parents("form.datagrid").find("input:submit[name=filterSubmit]").nellaSubmit();
			return false;
		}
	});
	// datagrid filter ajax change state selectbox or checkbox
	jQuery("form.datagrid table.datagrid").find("tr.filters input:checkbox, tr.filters select").livequery("change", function (e) {
		jQuery(this).parents("form.datagrid").find("input:submit[name=filterSubmit]").nellaSubmit();
		return false;
	});
	// datagrid paginator ajax press <ENTER>
	jQuery("form.datagrid table.datagrid tr.footer input[name=pageSubmit]").livequery(function () {
		jQuery(this).hide();
	});
	jQuery("form.datagrid table.datagrid tr.footer input[name=page]").livequery("keypress", function (e) {
		if (e.keyCode == 13) {
			jQuery(this).parents("form.datagrid").find("input:submit[name=pageSubmit]").nellaSubmit();
			return false;
		}
	});
	//datagrid paginator ajax change state selectbox
	jQuery("form.datagrid table.datagrid tr.footer input[name=itemsSubmit]").livequery(function () {
		jQuery(this).hide();
	});
	jQuery("form.datagrid table.datagrid tr.footer select[name=items]").livequery("change", function (e) {
		jQuery(this).parents("form.datagrid").find("input:submit[name=itemsSubmit]").nellaSubmit();
	});
});