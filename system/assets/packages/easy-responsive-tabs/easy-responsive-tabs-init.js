$(document).ready(function () {
    $('.horizontal-tabs').easyResponsiveTabs({
        type: 'default', //Types: default, vertical, accordion           
        width: 'auto', //auto or any width like 600px
        fit: true,   // 100% fit in a container
        closed: 'accordion', // Start closed if in accordion view
    });

    $('.vertical-tabs').easyResponsiveTabs({
        type: 'vertical',
        width: 'auto',
        fit: true,
    });
});