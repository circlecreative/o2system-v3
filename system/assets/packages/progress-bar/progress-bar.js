// Global
$(function(){
    $(document).ajaxStart(function() {
        //only add progress bar if added yet.
        if ($("#progress-bar").length === 0) {
            $("body").append($("<div><dt/><dd/></div>").attr("id", "progress-bar"));
            $("#progress-bar").width((50 + Math.random() * 30) + "%");
        }
    });

    $(document).ajaxComplete(function() {
        //End loading animation
        $("#progress-bar").width("101%").delay(200).fadeOut(400, function() {
            $(this).remove();
        });
    });

    $(document).ready(function() {
        if ($("#progress-bar").length === 0) {
            $("body").append($("<div><dt/><dd/></div>").attr("id", "progress-bar"));
            $("#progress-bar").width((50 + Math.random() * 30) + "%");
        }
    }); 

    $(window).load(function() {
        // executes when complete page is fully loaded, including all frames, objects and images
        //End loading animation
        $("#progress-bar").width("101%").delay(200).fadeOut(400, function() {
            $(this).remove();
        });
        $('#status').fadeOut(); // will first fade out the loading animation
        $('#preloader').delay(400).fadeOut('slow'); // will fade out the white DIV that covers the website.
    });
});