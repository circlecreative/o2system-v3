$(function() {
	$(".treeview").jstree({
		// the `plugins` array allows you to configure the active plugins on this instance
		"plugins" : ["themes","html_data","ui","crrm"],
		// each plugin you have included can have its own config object
		"core" : {
			"animation" : 100,
			"initially_open" : [ "phtml_1" ]
		},
		// set a theme
		"themes" : {
            "theme" : "proton"
        },
	})
});