$(document).ready(function(){
  $('.treeview').treeview({control:'.treeview-control'});

  $('.tree-folder-action').click(function(){
  	var folderName = '';
  	if (!$(this).attr('folder-open') || $(this).attr('folder-open') == 'off'){
       $(this).attr('folder-open','on');
       $(this).children('i').removeClass('fa-folder').addClass('fa-folder-open');
       folderName = $(this).children('.tree-folder-name').html();
    }
    else if ($(this).attr('folder-open') == 'on'){
       $(this).attr('folder-open','off');
       $(this).children('i').removeClass('fa-folder-open').addClass('fa-folder');
    }
  });
});