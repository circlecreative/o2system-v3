// Helper
(function( $ ) {
    $.getParams = function( sParam ) {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) 
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam) 
            {
                return sParameterName[1];
            }
        }
        return false;
    };
 
}( jQuery ));

// Page Preloader
$(function(){
    $(document).ajaxStart(function() {
        //only add progress bar if added yet.
        if ($("#preloader-bar").length === 0) {
            $("body").append($("<div><dt/><dd/></div>").attr("id", "preloader-bar"));
            $("#preloader-bar").width((50 + Math.random() * 30) + "%");
        }
    });

    $(document).ajaxComplete(function() {
        //End loading animation
        $("#preloader-bar").width("101%").delay(200).fadeOut(400, function() {
            $(this).remove();
        });
    });

    $(document).ready(function() {
        if ($("#preloader-bar").length === 0) {
            $("body").append($("<div><dt/><dd/></div>").attr("id", "preloader-bar"));
            $("#preloader-bar").width((50 + Math.random() * 30) + "%");
        }
    }); 

    $(window).load(function() {
        // executes when complete page is fully loaded, including all frames, objects and images
        //End loading animation
        $("#preloader-bar").width("101%").delay(200).fadeOut(400, function() {
            $(this).remove();
        });
        $('#status').fadeOut(); // will first fade out the loading animation
        $('#preloader').delay(400).fadeOut('slow'); // will fade out the white DIV that covers the website.
        $('body').delay(400).css({'overflow':'visible'});
    });

    $('html').niceScroll({
        cursorcolor: "#1FB5AD",
        cursorborder: "0px solid #fff",
        cursorborderradius: "2px",
        cursorwidth: "5px"
    });
});

// Navigation
$(function () {
    $('#nav-accordion').dcAccordion({
        eventType: 'click',
        autoClose: true,
        saveState: true,
        disableLink: true,
        speed: 'slow',
        showCount: false,
        autoExpand: true,
        classExpand: 'dcjq-current-parent'
    });
});

var Script = function () {
    $('.tool-minimize').click(function () {
        var el = $(this).parents(".panel").children(".panel-body");
        if ($(this).hasClass("fa-chevron-down")) {
            $(this).removeClass("fa-chevron-down").addClass("fa-chevron-up");
            el.slideUp(200);
        } else {
            $(this).removeClass("fa-chevron-up").addClass("fa-chevron-down");
            el.slideDown(200);
        }
    });
    $('.table-responsive').niceScroll({
            cursorcolor: "#1FB5AD",
            cursorborder: "0px solid #fff",
            cursorborderradius: "0px",
            cursorwidth: "3px"
        });
    $('.equalize').equalize({children: 'p'});
    $('.tooltips').tooltip();
    $('.popovers').popover();
}();