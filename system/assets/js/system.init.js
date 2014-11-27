// Core System JS

(function( $ ) {
    // Define SERVER Object
    $.SERVER = new Object();

    // BASE URL
    $.SERVER.BASE_URL = window.location.protocol + '//' + window.location.hostname;

    // SELF URL
    $.SERVER.SELF_URL = $.SERVER.BASE_URL + window.location.pathname;
    $.SERVER.SELF_URL = $.SERVER.SELF_URL.replace('.html','');

    // SERVER QUERY STRING
    $.SERVER.QUERY_STRING = window.location.search.substring(1);

    var QUERY_PARAMS = $.SERVER.QUERY_STRING.split('&');
    $.SERVER.GET = new Object();
    for (var i = 0; i < QUERY_PARAMS.length; i++) {
        var PARAM = QUERY_PARAMS[i].split('=');
        $.SERVER.GET[PARAM[0]] = PARAM[1];

        // UNSET PARAM
        delete PARAM;
    }

    // UNSET QUERY_PARAMS
    delete QUERY_PARAMS;

    $.http_build_query = function(QUERY) {
        if(typeof QUERY == 'undefined') {
            QUERY = $.SERVER.GET;
        }

        var build_query = '?';
        $.each(QUERY, function(key, value){
            if(value !== 'undefined' && value !== '' && key !== '') {
                build_query = build_query + key + '=' + value + '&';
            }
        });

        return build_query.substring( 0, (build_query.length-1) );
    }

    $.modal = $('#modal-dialog');
    $.modal.header = $.modal.find('.modal-header');
    $.modal.content = $.modal.find('.modal-content');
    $.modal.title = $.modal.find('.modal-title');
    $.modal.body = $.modal.find('.modal-body');
    $.modal.footer = $.modal.find('.modal-footer');

    $.inputGet = function( sParam ) {
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

    $.openWindow = function(url, title, width, height) {
        // Fixes dual-screen position Most browsers Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((screenWidth / 2) - (width / 2)) + dualScreenLeft;
        var top = ((screenHeight / 2) - (height / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'toolbar=no, menubar=no, resizable=no, copyhistory=no, location=no, directories=no, status=no, addressbar=0, scrollbars=no, width=' + width + ', height=' + height + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow
        if (window.focus) {
            newWindow.focus();
        }
    };

    $.MAIN = new Object();
    $.MAIN.innerWidth = $('.main-content').innerWidth();
    $.MAIN.outerWidth = $('.main-content').outerWidth();
    $.MAIN.width = $('.main-content').width();
    $.MAIN.paddingWidth = parseInt( $('.main-content').css('padding') );
    $.MAIN.marginWidth = parseInt( $('.main-content').css('margin') );
    $.MAIN.borderWidth = parseInt( $('.main-content').css('border') );
    console.log($.MAIN);
}( jQuery ));

$(document).ready(function(){
    var iDocument = $(document);
    var iWindow = $(window);
    var iHTML = $('html');
    var iBody = $('body');

    iWindow.find('a.hide-status').hover(function(){
        iWindow.status = '';
    }, function(){
        iWindow.status = '';
    });

    var iSingle = $('.single');
    if(typeof(iSingle) != 'undefined') {
        iSingle.find('.single-image>img').css('min-width',($.MAIN.width + $.MAIN.paddingWidth));
    }
});