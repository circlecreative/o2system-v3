$(function() {
    $('body').append('<div id="toTop" class="btn btn-primary"><span class="glyphicon glyphicon-chevron-up"></span></div>');
    $(window).scroll(function () {
        if ($(this).scrollTop() != 0) {
            $('#toTop').fadeIn();
        } else {
            $('#toTop').fadeOut();
        }
    }); 
    $('#toTop').click(function(){
        $("html, body").animate({ scrollTop: 0 }, 600);
        return false;
    });

    var toc = $("#toc").tocify({ selectors: "h2, h3, h4" }).data("toc-tocify");
    $('pre').addClass('prettyprint linenums');
    prettyPrint();
    $(".optionName").popover({ trigger: "hover" });
    $('#toc').niceScroll({
        cursorcolor: "#e73d2f",
        cursorborder: '2px solid #e73d2f',
        cursorborderradius: "2px",
        cursorwidth: "2px",
        horizrailenabled:false
    });

    $('a').click(function(){
        var href = $(this).attr('href');

        if(typeof href !== 'undefined')
        {
            var dataUnique = href.replace('#','');
            $('html, body').animate({
                scrollTop: $('div[name="'+dataUnique+'"]').offset().top
            }, 1000);
        }
    });
});