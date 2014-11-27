$(function() {

    setInterval( function() {
        var seconds = new Date().getSeconds();
        var sdegree = seconds * 6;
        var srotate = "rotate(" + sdegree + "deg)";

        $("#sec").css({"-moz-transform" : srotate, "-webkit-transform" : srotate});

    }, 1000 );


    setInterval( function() {
        var hours = new Date().getHours();
        var mins = new Date().getMinutes();
        var hdegree = hours * 30 + (mins / 2);
        var hrotate = "rotate(" + hdegree + "deg)";

        $("#hour").css({"-moz-transform" : hrotate, "-webkit-transform" : hrotate});

    }, 1000 );


    setInterval( function() {
        var mins = new Date().getMinutes();
        var mdegree = mins * 6;
        var mrotate = "rotate(" + mdegree + "deg)";

        $("#min").css({"-moz-transform" : mrotate, "-webkit-transform" : mrotate});

    }, 1000 );

    setInterval( function() {
        var hours = new Date().getHours();
        var mins = new Date().getMinutes();
        var seconds = new Date().getSeconds();
        var meridiem = ( hours < 12 ) ? 'AM' : 'PM';

        hours = ( hours < 10 ? '0' : '' ) + hours;
        mins = ( mins < 10 ? '0' : '' ) + mins;
        seconds = ( seconds < 10 ? '0' : '' ) + seconds;

        $('#clock-digit').html(hours + ':' + mins + ' ' + meridiem);
        
    }, 1000 );
});