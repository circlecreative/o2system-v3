$(function () {
    $('.branch-tree li:has(ul)').addClass('has-child').find(' > div');
    $('.branch-tree li.has-child > div').on('click', function (e) {
        var children = $(this).parent('li.has-child').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).find('i').addClass('fa-folder').removeClass('fa-folder-open');
        } else {
            children.show('fast');
            $(this).find('i').addClass('fa-folder-open').removeClass('fa-folder');
        }
        e.stopPropagation();
    });
});