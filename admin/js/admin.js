/*
Title:				Admin Panel JavaScript
Author:				Sam Rayner - http://samrayner.com
Created:			2011-09-16
*/

var Icons = {
	removeAll: function($elm) {
		$elm.removeClass(function(index, css) {
			return(css.match(/\bicon-\S+/g) || []).join(' ');
		});
	}
};

var PageNav = {
	sortableTree: function(parents) {
		var tree = {};
	
		for(var i = 0; i < parents.length; i++) {
			var parent = parents[i];
			tree[parent] = PageNav.sortableTree($("#"+parent+" > .sortable").sortable("toArray"));
			if(!$("#"+parent+" > * > input:checked").length) {
				tree[parent]._hidden = true;
			}
		}
		
		return tree;
	},

	removePropPrefix: function(object, prefix) {
		var newObject = {};

		for(var propName in object) {
			var regex = new RegExp('^'+prefix,"i");
			var newPropName = propName.replace(regex, '');

			if(typeof object[propName] !== "object" || $.isEmptyObject(object[propName])) {
				newObject[newPropName] = object[propName];
			}
			else {
				newObject[newPropName] = PageNav.removePropPrefix(object[propName], prefix);
			}
		}

		return newObject;
	},
	
	updateOrder: function() {
		var topLevel = $("#page-list > ol").sortable("toArray");
		var tree =  PageNav.sortableTree(topLevel);
		var pages = PageNav.removePropPrefix(tree, "page_");

		$("#page_order").val(JSON.stringify(pages));
	},
	
	updateVisibility: function(event) {
		//get whether we're checking or unchecking
		var checking = event.target.checked;

		//for all child checkboxes
		$(this).closest("li").find("* * input:checkbox").each(function() {
			//if we're unchecking, disable all children
			if(!checking) {
				$(this).attr("disabled", true);
			}
			
			//if checking
			else {
				//get all grand-parent LIs
				var $parentLis = $(this).closest("li").parentsUntil($("#page-list"),"li");
				
				var parentsUnchecked = $parentLis.children("div").children("input:not([checked])").length;
				
				//if we're checking, enable children who's parents are enabled
				if(!parentsUnchecked) {
					$(this).removeAttr("disabled");
				}
			}
		});
		
		PageNav.updateOrder();
	},
	
	labelTap: function() {
		var $checkbox = $("#".$(this).attr("for"));
		var checked = $checkbox.checked;
		
		if(checked) {
			$checkbox.removeAttr("checked");
		}
		else {
			$checkbox.attr("checked", 1);
		}
	},
	
	init: function() {
		$("#page-list input:checkbox").change(PageNav.updateVisibility);
		$("#page-list label").click(PageNav.labelTap);
	
		//disable text selection so we can drag
		$("#page-list > ol").disableSelection();
		
		//make lists sortable
		$(".sortable").sortable({
				update: PageNav.updateOrder
		});
		
		//fire change on every checkbox
		$(".sortable input:checkbox").change();

		if(!('ontouchstart' in document.documentElement)) {
			$("#page-nav .subtitle").append(", drag to reorder");
		}
	}
};

var Tooltips = {
	toggle: function(selector) {
		$(selector).toggleClass("collapsed");
	},

	init: function() {
		$("#pretty_urls").change(function(){
			Tooltips.toggle("#htaccess");
		});
	}
};

var Recache = {
	types: ["collections", "snippets", "pages", "posts", "drafts"],

	done: function() {
		var $btn = $("#recache-button");

		Icons.removeAll($btn);

		$btn
			.removeClass("active")
			.removeAttr("style")
			.addClass("done icon-ok-sign")
			.html("Caching complete")
			.click(Recache.click);
	},

	updateProgress: function(type) {
		var index = Recache.types.indexOf(type);
		var percent = (index+1)*100/Recache.types.length;
	
		$("#recache-button").html("Caching "+type+"&hellip;");
		
		var width = $("#recache-button").outerWidth();
		
		$("#recache-button").css("background-position", Math.round(width*percent/100)+"px, 50%");
	},
	
	process: function(type) {
		var pos = Recache.types.indexOf(type);
	
		if(pos >= 0) {
			Recache.updateProgress(type);
		}
	
		var $jqxhr = $.ajax({
			url: "../admin/update/index.php",
			data: {"type": type},
			complete: function() {
				if(pos >= 0) {
					if(pos+1 === Recache.types.length) {
						Recache.done();
					}
					else {
						Recache.process(Recache.types[pos+1]);
					}
				}
			}
		});
	},

	block: function(event) {
		var $button = $("#recache-button");

		$button
			.addClass("disabled icon-ban-circle")
			.html("Save changes before caching")
			.off("click");
	},

	click: function(event) {
		event.preventDefault();

		var $this = $(this);
		
		Icons.removeAll($this);

		$this
			.removeClass("done")
			.addClass("active icon-refresh")
			.off("click");

		Recache.process(Recache.types[0]);
	},
	
	init: function() {
		$("#host_root").change(Recache.block);
		$("#recache-button").click(Recache.click);
	}
};

var FormCheck = {
	required: function(event) {
		var failed = $(this).find("input[required]").filter(function(){
			return ($(this).val().trim() === "");
		});
		
		$.each(failed, function(){
			$(this).addClass("highlight");
		});
		
		if(failed.length > 0) {
			failed[0].focus();
			event.preventDefault();
			return false;
		}
	},

	verifyPass: function(event) {
		if($("#admin_password").val() !== $("#confirm_pass").val()) {
			window.alert("The passwords you entered don't match, please type them again.");
			$("#admin_password").val("").addClass("highlight").focus();
			$("#confirm_pass").val("").addClass("highlight");
			event.preventDefault();
			return false;
		}
	},

	init: function() {
		$("form").submit(FormCheck.required);
		$("form").submit(FormCheck.verifyPass);
	}
};

$(function() {
	Tooltips.init();
	FormCheck.init();
	
	if($("#page-list").length) {
		PageNav.init();
	}
	
	Recache.init();
});