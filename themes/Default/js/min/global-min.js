/*! matchMedia() polyfill - Test a CSS media type/query in JS. Authors & copyright (c) 2012: Scott Jehl, Paul Irish, Nicholas Zakas. Dual MIT/BSD license */window.matchMedia=window.matchMedia||function(a,b){var c,d=a.documentElement,e=d.firstElementChild||d.firstChild,f=a.createElement("body"),g=a.createElement("div");g.id="mq-test-1";g.style.cssText="position:absolute;top:-100em";f.appendChild(g);return function(a){g.innerHTML='&shy;<style media="'+a+'"> #mq-test-1 { width: 42px; }</style>';d.insertBefore(f,e);c=g.offsetWidth===42;d.removeChild(f);return{matches:c,media:a}}}(document);var DropdownNav={listToArray:function(a){var b=[];$.each($(a).children("li"),function(a,c){var d=$(this).children("a:first");b[a]={title:d.text()};b[a].href=d.attr("href");b[a].current=$(this).hasClass("current");var e=$(this).children("ul:first");e.length&&(b[a].subpages=DropdownNav.listToArray(e.get()))});return b},addOptions:function(a,b,c){$.each($(b),function(b,d){var e=$("<option />");e.attr("value",d.href);e.attr("selected",d.current);var f=new Array(c+1);f=f.join("&rarr; ");e.html(f+d.title);a.append(e);d.subpages&&DropdownNav.addOptions(a,d.subpages,c+1)})},init:function(){var a=$("nav.page-nav ul:first"),b=DropdownNav.listToArray(a.get()),c=$("<select />");DropdownNav.addOptions(c,b,0);c.change(function(){window.location.href=c.children("option:selected").val()});a.replaceWith(c)}};$(function(){window.matchMedia("screen and (max-width: 700px)").matches&&DropdownNav.init()});