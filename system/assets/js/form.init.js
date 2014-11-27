// Toastr
toastr.options = {
    "closeButton": true,
    "debug": false,
    "positionClass": "toast-top-right",
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

// Color Picker
$('.colorpicker-default').colorpicker({
    format: 'hex'
});
$('.colorpicker-rgba').colorpicker();

// Tree View
$('.treeview').treeview({
    control:'.treeview-control'
});

// Split List
var colSplit = $('.split-list').attr('split');
$('.split-list').easyListSplitter({ 
    colNumber: colSplit, // Insert here the number of columns you want. Consider that the plugin will create the number of cols requested only if there's enough items in the list.
    direction: 'horizontal'
});

// Action Button
$('[btn-action="save"]').click(function(){
    $('form').submit();
});

// Clone Input
$('.input-group').on('click','.input-clone',function(){
    var cloneInput = $(this).parents('.input-group').clone().css('padding-top','15px');
    cloneInput.children('.input-clone').removeClass('input-clone').addClass('remove-clone').children('.fa').removeClass('fa-plus').addClass('fa-trash-o');
    cloneInput.find('input').val('');
    $(this).parents('.input-group').after(cloneInput);

    $('.input-group').on('click','.remove-clone',function(){
        $(this).parents('.input-group').remove();
    });
});

$('.input-group').on('click','.remove-clone',function(){
    $(this).parents('.input-group').remove();
});

// Clone Input Table
$('.input-table').on('click','.input-table-clone',function(){
    var iRel = $(this).attr('rel');
    var templateTR = $('#' + iRel + '-template').clone();
    var countTR = $('#' + iRel + '-tbody>tr').length;

    templateTR.removeClass('hidden');
    templateTR.find('input').val('');
    templateTR.find('[name]').each(function(i, input){
        var iName = $(this).attr('name');
        iName = iName.replace('0', countTR);
        templateTR.find('[name="'+ $(this).attr('name') +'"]').attr('name', iName);
    });
    templateTR.attr('id','');

    templateTR.find('.datepicker').each(function(i, el)
    {
        var $this = $(el),
            opts = {
                format: attrDefault($this, 'format', 'mm/dd/yyyy'),
                startDate: attrDefault($this, 'startDate', ''),
                endDate: attrDefault($this, 'endDate', ''),
                daysOfWeekDisabled: attrDefault($this, 'disabledDays', ''),
                startView: attrDefault($this, 'startView', 0),
                rtl: rtl()
            },
            $n = $this.next(),
            $p = $this.prev();
                        
        $this.datepicker(opts);
        
        if($n.is('.input-group-addon') && $n.has('a'))
        {
            $n.on('click', function(ev)
            {
                ev.preventDefault();
                
                $this.datepicker('show');
            });
        }
        
        if($p.is('.input-group-addon') && $p.has('a'))
        {
            $p.on('click', function(ev)
            {
                ev.preventDefault();
                
                $this.datepicker('show');
            });
        }
    });

    templateTR.find('.daterange').each(function(i, el)
    {
        // Change the range as you desire
        var ranges = {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'Last 30 Days': [moment().subtract('days', 29), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
        };
        
        var $this = $(el),
            opts = {
                format: attrDefault($this, 'format', 'MM/DD/YYYY'),
                timePicker: attrDefault($this, 'timePicker', false),
                timePickerIncrement: attrDefault($this, 'timePickerIncrement', false),
                separator: attrDefault($this, 'separator', ' - '),
            },
            min_date = attrDefault($this, 'minDate', ''),
            max_date = attrDefault($this, 'maxDate', ''),
            start_date = attrDefault($this, 'startDate', ''),
            end_date = attrDefault($this, 'endDate', '');
        
        if($this.hasClass('add-ranges'))
        {
            opts['ranges'] = ranges;
        }   
            
        if(min_date.length)
        {
            opts['minDate'] = min_date;
        }
            
        if(max_date.length)
        {
            opts['maxDate'] = max_date;
        }
            
        if(start_date.length)
        {
            opts['startDate'] = start_date;
        }
            
        if(end_date.length)
        {
            opts['endDate'] = end_date;
        }
        
        
        $this.daterangepicker(opts, function(start, end)
        {
            var drp = $this.data('daterangepicker');
            
            if($this.is('[data-callback]'))
            {
                //daterange_callback(start, end);
                callback_test(start, end);
            }
            
            if($this.hasClass('daterange-inline'))
            {
                $this.find('span').html(start.format(drp.format) + drp.separator + end.format(drp.format));
            }
        });
    });

    templateTR.find('.timepicker').each(function(i, el)
    {
        var $this = $(el),
            opts = {
                template: attrDefault($this, 'template', false),
                showSeconds: attrDefault($this, 'showSeconds', false),
                defaultTime: attrDefault($this, 'defaultTime', 'current'),
                showMeridian: attrDefault($this, 'showMeridian', true),
                minuteStep: attrDefault($this, 'minuteStep', 15),
                secondStep: attrDefault($this, 'secondStep', 15)
            },
            $n = $this.next(),
            $p = $this.prev();
        
        $this.timepicker(opts);
        
        if($n.is('.input-group-addon') && $n.has('a'))
        {
            $n.on('click', function(ev)
            {
                ev.preventDefault();
                
                $this.timepicker('showWidget');
            });
        }
        
        if($p.is('.input-group-addon') && $p.has('a'))
        {
            $p.on('click', function(ev)
            {
                ev.preventDefault();
                
                $this.timepicker('showWidget');
            });
        }
    });
    
    templateTR.find('.bootstrap-select').remove();
    templateTR.find('.selectpicker').selectpicker();

    templateTR.find('.select2-container').remove();
    templateTR.find('select.select2').select2({
        minimumInputLength: 2
    });
    
    $('#' + iRel + '-tbody').append(templateTR);
});

$('.input-table').on('click','.input-table-remove',function(){
    $(this).closest('tr').remove();
    var iRel = $(this).attr('rel');
    var countTR = $('#' + iRel + '-tbody>tr').length - 1;

    if(countTR == 0){
        var templateTR = $('#' + iRel + '-template').clone();
        templateTR.removeClass('hidden');
        templateTR.find('input').val('');
        templateTR.attr('id','');
        $('#' + iRel + '-tbody').append(templateTR);
    }
});

// Switch
$.fn.bootstrapSwitch.defaults.size = 'mini';
$('.switch').bootstrapSwitch();
$('.switch').on('switchChange.bootstrapSwitch', function(event, state) {
    $(this).attr('checked', state);
});

// ICheck
$('input.icheck').iCheck({
    checkboxClass: 'icheckbox_minimal-grey',
    radioClass: 'iradio_minimal-grey'
});

//spinner start
$('.spinner').spinner('changed', function(e, newVal, oldVal){
    $(this).val(newVal);
});
//spinner end

// Range Slider
$(".range-slider").ionRangeSlider({
    min: $(this).attr('min'),
    max: $(this).attr('max'),
    step: $(this).attr('step'),
    type: $(this).attr('type'),
    prefix: $(this).attr('prefix'),
    postfix: $(this).attr('postfix'),
    values: $(this).attr('values'),
    from: 'min',
    prettify: true,
    hasGrid: true,
});

$('.fileinput').fileinput();

// CKEditor
CKEDITOR.disableAutoInline = true;
$('.ckeditor-basic').ckeditor({
    skin: 'bootstrapck',
    'toolbar' : [
        ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','NumberedList','BulletedList','Indent','Outdent','-','TextColor','BGColor','-','RemoveFormat','Source'],
    ],
    
    // Enter
    enterMode : CKEDITOR.ENTER_BR,
    shiftEnterMode: CKEDITOR.ENTER_P
});

$('.ckeditor').ckeditor({
    skin: 'bootstrapck',
    extraPlugins: 'codemirror',
    'toolbar' : [
        ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','NumberedList','BulletedList','Indent','Outdent','-','Undo', 'Redo' ,'-','Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'RemoveFormat','-','Find', 'Replace'],
        '/',
        ['Format', 'FontSize' ],['TextColor','BGColor'],[ 'Link', 'Unlink', 'Anchor','-','Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ],[ 'SelectAll', 'Maximize','-','Source']
    ],
    // Enter
    enterMode : CKEDITOR.ENTER_BR,
    shiftEnterMode: CKEDITOR.ENTER_P
});

$('[action="insert-code"]').click(function(){
    CKEDITOR.instances[$(this).attr('rel')].insertText('test code');
});

// TinyMCE
$('.tinymce').tinymce({
        skin: 'light',
        plugins: [
                "advlist autolink autosave link lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons template textcolor paste fullpage textcolor"
        ],

        toolbar1: "newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
        toolbar2: "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | inserttime preview | forecolor backcolor",
        toolbar3: "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking template pagebreak restoredraft",

        menubar: false,
        toolbar_items_size: 'small',

        style_formats: [
                {title: 'Bold text', inline: 'b'},
                {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
                {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
                {title: 'Example 1', inline: 'span', classes: 'example1'},
                {title: 'Example 2', inline: 'span', classes: 'example2'},
                {title: 'Table styles'},
                {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
        ],

        templates: [
                {title: 'Test template 1', content: 'Test 1'},
                {title: 'Test template 2', content: 'Test 2'}
        ]
});

$('.tinymce-post-editor, .tinymce-page-editor').tinymce({
    skin: 'light',
    plugins: [
        "advlist autolink autosave link lists charmap print preview hr anchor pagebreak spellchecker",
        "searchreplace wordcount fullscreen insertdatetime media nonbreaking",
        "table contextmenu directionality emoticons template textcolor paste fullpage textcolor"
    ],

    toolbar1: "undo redo | bold italic underline strikethrough subscript superscript blockquote | alignleft aligncenter alignright alignjustify | bullist numlist indent outdent | table link unlink anchor image media",
    toolbar2 : "formatselect fontsizeselect | spellchecker searchreplace cut copy paste removeformat | forecolor backcolor | charmap emoticons | nonbreaking hr pagebreak | code fullscreen",

    menubar: false,
    toolbar_items_size: 'small'
});

// Redactor
$('.redactor').redactor({
    imageGetJson: '/webapps/patrakom-erp/images/lists.html',
    imageUpload: '/webapps/patrakom-erp/images/upload.html',
    fileUpload: '/webapps/patrakom-erp/files/upload.html'
});

// Code Editor
$('.text-editor').ace({theme: 'twilight', lang: 'text'});
$('.html-editor').ace({theme: 'twilight', lang: 'html'});
$('.js-editor').ace({theme: 'twilight', lang: 'javascript'});
$('.css-editor').ace({theme: 'twilight', lang: 'css'});
$('.less-css-editor').ace({theme: 'twilight', lang: 'less'});
$('.php-editor').ace({theme: 'twilight', lang: 'php'});
$('.xml-editor').ace({theme: 'twilight', lang: 'xml'});
$('.json-editor').ace({theme: 'twilight', lang: 'json'});
$('.sql-editor').ace({theme: 'twilight', lang: 'sql'});

$(document).ready(function(){
    $('.ace_editor').css({
        'min-height':'100px',
        'width':'auto'
    });
});

// Multiselect
$('.multi-select').multiSelect({
    selectableOptgroup: true
});

// Multiselect Search
$('.multi-select-search').multiSelect({
    selectableHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
    selectionHeader: "<input type='text' class='form-control search-input' autocomplete='off' placeholder='search...'>",
    afterInit: function (ms) {
        var that = this,
            $selectableSearch = that.$selectableUl.prev(),
            $selectionSearch = that.$selectionUl.prev(),
            selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
            selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

        that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
            .on('keydown', function (e) {
                if (e.which === 40) {
                    that.$selectableUl.focus();
                    return false;
                }
            });

        that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
            .on('keydown', function (e) {
                if (e.which == 40) {
                    that.$selectionUl.focus();
                    return false;
                }
            });
    },
    afterSelect: function () {
        this.qs1.cache();
        this.qs2.cache();
    },
    afterDeselect: function () {
        this.qs1.cache();
        this.qs2.cache();
    }
});

// Depended Dropdown
$('.depended-dropdown').change(function(){
    var childID = $(this).attr('data-child');
    var sourceDB = $(this).attr('data-source');
    var URL = $(this).attr('data-url');
    var conditions = new Array();
    var _conditions = $(this).attr('data-conditions');

    _conditions = _conditions.split(',');

    $.each(_conditions,function(i,condition){
        var _condition = condition.split(':');
        var _conditionVal = $('#' + _condition[1]).val();

        if(_conditionVal != '0' || _conditionVal != '' || _conditionVal != 'null' || _conditionVal != 'undefined'){
            conditions[i] = _condition[0] + ':' + _conditionVal;
        }
        
    });

    $.post(URL,{
        'sourceDB' : sourceDB,
        'conditions' : conditions,
    },function(response){
        var Root = $('#' + childID).children('option').first()[0].outerHTML;
        response = Root + response;
        $('#' + childID).html(response);
    });
});

// Depended Tab
$('.depended-tab').change(function(){
    var URL = $(this).attr('tab-url');
    var Target = $(this).attr('tab-target');
    var Request = $(this).val();
    $.get(URL,{request:Request},function(response){
        $('#content-' + Target).html(response);
    });
});

// Depended Container
$(document).ready(function(){
    var iControl = $('.container-control'); 
    var iContainers = iControl.attr('data-containers');
    var iActive = iControl.val();

    if(typeof(iContainers) != 'undefined') {
        iContainers = iContainers.replace(/'/gi,'"');
        iContainers = JSON.parse(iContainers);

        $.each(iContainers, function(i, iTarget){
            $('#container-' + iTarget).addClass('depended-container').hide();
            if(iTarget == iActive){
                $('#container-' + iTarget).show();
            }
        }); 

        iControl.change(function(){
            var iTarget = $(this).val();
            $('.depended-container').hide();
            $('#container-'+ iTarget).show();
        });
    }
});

// Table Form
$('body').on('click','.form-table-dialog',function(){
    var iRel = $(this).attr('rel');
    var iForm = $('#' + iRel + '-form-dialog').clone();
    var iCount = $('.' + iRel + '-row').length;

    iForm.removeClass('hidden');
    iForm.attr('data-name')
    $('#modal-title').html('Form Dialog');
    $('#modal-body').html(iForm).css('padding','0px');
    $('#modal-dialog').modal('show');
    $('.modal-dialog').css('width','90%');
});

$('body').on('click','.form-table-action',function(){
    var iAction = $(this).attr('item-action');
    var iTable = $(this).parents('table').attr('id');
    var iRow = $(this).parents('tr');
    var iForm = $('#' + iTable + '-form-dialog').clone();
    //console.log('iAction:' + iAction);
    //console.log('iTable:' + iTable);
    //console.log(iRow);
    //console.log(iForm);
    if(iAction == 'edit'){
        var iDataID = iRow.attr('data-id');
        //console.log('iDataID:' + iDataID);
        var iData = iRow.find('input').val();
        iData = iData.replace(/'/gi,'"');
        //console.log(iData);
        iData = JSON.parse(iData);
        //console.log(iData);

        $.each(iData,function(name, data){
            iForm.find('[name="'+name+'"]').val(data);
        });

        iForm.removeClass('hidden');
        iForm.attr('data-id',iDataID);
        $('#modal-title').html('Form Dialog');
        $('#modal-body').html(iForm).css('padding','0px');
        $('.modal-dialog').css('width','90%');
        $('#modal-dialog').modal('show');
    } else if(iAction == 'delete') {
        iRow.remove();
    }
});

// Save Form
$('#modal-body').on('click','[btn-action="save"]',function(){
    event.preventDefault();
 
    var iForm = $(this).parents('.modal-dialog-form').attr('rel');
    var iTable = $(this).parents('.modal-dialog-form').attr('id');
    var iDataID = $(this).parents('.modal-dialog-form').attr('data-id');
    var iDataName = $(this).parents('.modal-dialog-form').attr('data-name');
    iTable = iTable.replace('-form-dialog','');

    //console.log('iTable:' + iTable);
    //console.log('iForm:' + iForm);
    //console.log('iDataID:' + iDataID);
    //console.log('iDataName:' + iDataName);

    var iCount = $('.' + iTable + '-row').length;

    //console.log('iCount:' + iCount);

    var iData = new Object();
    var iDataTitle = '';
    
    var iFields = $(this).parents('.modal-dialog-form').find('.form-control');
    //console.log('iCountFields:' + iFields.length);
    //console.log(iFields);

    $.each(iFields,function(i){
        var iName = $(this).attr('name');
        var iVal = $(this).val();
        iData[iName] = iVal;
        if(iName == iDataName) {
            iDataTitle = iVal;
        }
    });
    //console.log('iDataTitle:' + iDataTitle);
    //console.log(iData);
    iData = JSON.stringify(iData);

    if(typeof iDataID == 'undefined' || iDataID == false){
        //console.log('Add New Row:'+iDataID);
        var iRow = $('#' + iTable + '-row').clone();
        iRow.removeAttr('id')
        iRow.removeClass('hidden');
        iRow.attr('data-id',iCount+1);
        iRow.find('[data-content="title"]').html(iDataTitle);
        iRow.find('input').attr('value',iData).attr('data-id',iCount+1);
        $('#' + iTable + '>tbody').append(iRow);
    } else {
        console.log('Update Row: '+iDataID);
        var iRows = $('#' + iTable + '>tbody');
        //console.log(iRows);
        var iRow = iRows.find('tr[data-id="'+iDataID+'"]');
        //console.log(iRow);
        iRow.find('[data-content="title"]').html(iDataTitle);
        iRow.find('input').attr('value',iData);
    }

    $('#modal-dialog').modal('hide');
});

// Reset Form
$('#modal-body').on('click','[btn-action="reset"]',function(){
    event.preventDefault();
    var iForm = $(this).parents('.modal-dialog-form');
    iForm.find('.form-control').val('');
});

// Cancel Form
$('form').on('click','[btn-action="cancel"]',function(){
    event.preventDefault();
    var URL = $('#input-redirect').val();
    if(URL != '') window.location.href = URL;
});
$('#modal-body').on('click','[btn-action="cancel"]',function(){
    event.preventDefault();
    $('#modal-dialog').modal('hide');
});

$('[btn-action="delete"]').click(function(event){
    event.preventDefault();
});

// Prettyprint
$(document).ready(function(){
    prettyPrint();
});

// File Upload
$('.file-type, .video-type').click(function(event){
    // Prevent Default
    event.preventDefault();

    var Rel = $(this).attr('rel');
    var fileType = $(this).attr('data-type');

    // Change icon
    var Icon = $(this).children('i').attr('class');
    $('#' + Rel + '-icon').attr('class', Icon);
    $('#' + Rel + '-type').attr('value', fileType);

    if(fileType == 'localhost'){
        $('#' + Rel + '-url-field').addClass('hidden');
        $('#' + Rel + '-localhost-trigger').removeClass('hidden');
        $('#' + Rel + '-localhost-field').removeClass('hidden');
    } else {
        $('#' + Rel + '-url-field').removeClass('hidden');
        $('#' + Rel + '-localhost-trigger').addClass('hidden');
        $('#' + Rel + '-localhost-field').addClass('hidden');
    }
});

$('.file-upload').click(function(){
    var iForm = $(this).closest('form');
    var iRel = $(this).attr('rel');
    var iBar = $('#' + iRel + '-bar');
    var iStatus = $('#' + iRel + '-status');
    var iThis = $(this);
    var iParent = $(this).parents('.fileinput-group');
       
    $(iForm).ajaxSubmit({
        url: $(this).attr('data-url'),
        dataType : 'json',
        beforeSend: function() {
            iStatus.empty();
            iBar.css('width','0%');
        },
        uploadProgress: function(event, position, total, percentComplete ) {
            var iProgress = percentComplete + '%';
            iBar.css('width',iProgress);
            iStatus.html(percentComplete);
        },
        complete: function(xhr) {
            var response = xhr.responseJSON;

            if(response.success == true) {
                $('#' + iRel + '-large').attr('href',response.LargeSRC).children('img').attr('src',response.ThumbSRC);
                $('#' + iRel + '-info').attr('data-source',response.InfoURL);
                $('#' + iRel + '-filename').html(response.FileName);
                $('#' + iRel + '-input').attr('value', response.UploadPath);
                
                iThis.hide();
                iParent.find('.fileinput-preview').addClass('fancybox-image');
                iParent.find('.fileinput-new').hide();
                iParent.find('.fileinput-change').show();
                iParent.find('.fileinput-properties').show();
                iParent.find('.fileinput-remove').show();
            } else if(response.error == true) {
                toastr.error(response.message);
            }

            iForm.resetForm();  // reset form
            iStatus.empty();
            iBar.removeAttr('style');
        },
    });
});

$('.fileinput-upload').click(function(){
    var iParent = $(this).parents();
    $(this).parents().siblings('.file-upload').show();
    $(this).siblings('.fileinput-change').show();
    $(this).siblings('.fileinput-new').hide();
});

$('.fileinput-remove').click(function(){
    var URL = $(this).attr('data-url');
    var iRel = $(this).attr('rel');
    var iName = $('#'+iRel+'-input').val();
    var iThis = $(this);
    var iParent = $(this).parents('.fileinput-group');
    $.post(URL,{filename:iName}, function(response){
        if(response.success == true) {
            $('#' + iRel + '-large').removeAttr('href').children('img').attr('src',response.ThumbSRC);
            $('#' + iRel + '-info').removeAttr('data-source');
            $('#' + iRel + '-filename').html('');
            $('#' + iRel + '-input').attr('value','');
            iParent.find('.fileinput-preview').removeClass('fancybox-image');
            iParent.find('.fileinput-change').hide();
            iParent.find('.fileinput-properties').hide();
            iParent.find('.fileinput-remove').hide();
            iParent.find('.fileinput-new').show();
            toastr.success(response.message);
        } else if(response.error == true) {
            toastr.error(response.message);
        }
    },'json');
});

var iFileInput = $('.fileinput-filename');
$.each(iFileInput, function(){
    iParent = $(this).parents('.fileinput-group');
    if($(this).html() == '') {
        iParent.find('.fileinput-new').show();
        iParent.find('.fileinput-change').hide();
        iParent.find('.fileinput-properties').hide();
        iParent.find('.fileinput-remove').hide();
    } else {
        iParent.find('.fileinput-new').hide();
        iParent.find('.fileinput-change').show();
        iParent.find('.fileinput-properties').show();
        iParent.find('.fileinput-remove').show();
    }
});

// Image Properties
$('.image-properties').click(function(){
    var URL = $(this).attr('data-source');

    $('#modal-dialog').modal('show', {backdrop: 'static'});
    
    $.ajax({
        url: URL,
        success: function(response)
        {
            $('#modal-dialog .modal-header').hide();
            $('#modal-dialog .modal-body').html(response).addClass('clear-padding');
            $('#modal-dialog .modal-footer').hide();
        }
    });
});

// Image Library
var container = $('.gallery');
container.isotope({
    itemSelector: '.item',
    animationOptions: {
        duration: 750,
        easing: 'linear',
        queue: false
    }
});

$('.images-library-preview').on({
  click: function() {
    var checkbox = $(this).find('input[type="checkbox"]');
    if(checkbox.prop('checked')){
        checkbox.removeClass('checked');
        checkbox.closest('div').removeClass('checked');
        checkbox.parents('.item-checkbox').iCheck('uncheck').hide();
    } else {
        checkbox.addClass('checked');
        checkbox.closest('div').addClass('checked');
        checkbox.parents('.item-checkbox').iCheck('check').show()
    }
  }, mouseenter: function() {
    $(this).children('.item-checkbox').show();
  }, mouseleave: function() {
    var checkbox = $(this).find('input[type="checkbox"]');
    if(checkbox.prop('checked')){
        $(this).children('.item-checkbox').show();
    } else {
        $(this).children('.item-checkbox').hide();
    }
  }
},'.item');

$('form').on('click','.btn-select-img', function(){
/*    var iWidth = $(window).width() / 2;
    var iHeight = $(window).height() - 100;

    iDialog = $('#modal-dialog');

    iDialog.modal('show', {backdrop: 'static'});

    iDialog.find('.modal-header').hide();
    iDialog.find('.modal-footer').hide();
    

    var iFrame = $('<iframe frameborder="0"></iframe>');
    iFrame.attr('src', BASE_URL + 'plugins/media/image/pop-up.html');
    iFrame.attr('width',iWidth);
    iFrame.attr('height',iHeight);

    iDialog.find('.modal-body').html(iFrame).addClass('clear-padding').css({
        'width':iWidth,
        'height':iHeight
    });*/
    var iWidth = $(window).width() - 50;
    var iHeight = $(window).height() - 50;

    $.fancybox.open({
        href: BASE_URL + 'plugins/media/image/pop-up.html',
        type: 'iframe',
        padding:0,
        margin:0,
        maxWidth    : iWidth,
        maxHeight   : iHeight,
        fitToView   : false,
        width       : iWidth,
        height      : iHeight,
        autoSize    : false,
        closeClick  : false,
        openEffect  : 'none',
        closeEffect : 'none',
        afterLoad  : function () {
            $('form').parents('body').removeClass('loaded');
        }
    });
});


$('#modal-body').on({
  click: function() {
    var checkbox = $(this).find('input[type="checkbox"]');
    if(checkbox.prop('checked')){
        checkbox.removeClass('checked');
        checkbox.closest('div').removeClass('checked');
        checkbox.parents('.item-checkbox').iCheck('uncheck').hide();
    } else {
        checkbox.addClass('checked');
        checkbox.closest('div').addClass('checked');
        checkbox.parents('.item-checkbox').iCheck('check').show()
    }
  }, mouseenter: function() {
    $(this).children('.item-checkbox').show();
  }, mouseleave: function() {
    var checkbox = $(this).find('input[type="checkbox"]');
    if(checkbox.prop('checked')){
        $(this).children('.item-checkbox').show();
    } else {
        $(this).children('.item-checkbox').hide();
    }
  }
},'.item');

$('#modal-body').on({
  click: function() {
    var action = $(this).attr('action');
    var iBox = $('#dropbox-preview');

    if(action == 'select-all') {
        var checkbox = iBox.find('input[type="checkbox"]');
        $.each(checkbox, function(){
            if($(this).prop('checked')){
                $(this).removeClass('checked');
                $(this).closest('div').removeClass('checked');
                $(this).parents('.item-checkbox').iCheck('uncheck').hide();
            } else {
                $(this).addClass('checked');
                $(this).closest('div').addClass('checked');
                $(this).parents('.item-checkbox').iCheck('check').show();
            }
        });
    } else if(action == 'delete') {
        var checkbox = iBox.find('input[type="checkbox"]');
        var URL = $(this).attr('data-url');
        $.each(checkbox, function(){
            if($(this).prop('checked')){
                $.post(URL,{filename:$(this).val()},function(response){
                    $(this).parents('.item').remove();
                    $('.isotope').isotope( 'reloadItems' ).isotope( { sortBy: 'original-order' } );
                },'json');
            }
        });
    } else if(action == 'add-selected') {
        var checkbox = iBox.find('input[type="checkbox"]');
        var iParent = window.parent.$('.images-library-preview');
        var iRel = $('#modal-body').attr('rel');
        var URL = $(this).attr('data-url');
        iParent.show();
        var iPanel = iParent.parents('.images-library-wrapper').siblings('.images-library-panel');
        iPanel.find('a[action="select-all"]').show();
        iPanel.find('a[action="delete"]').show();
        $.each(checkbox, function(){
            if($(this).prop('checked')){
                var item = $(this).parents('.item').clone();
                item.find('input[type="checkbox"]').removeClass('checked').closest('div').removeClass('checked').parents('.item-checkbox').iCheck('uncheck').hide();
                iParent.append(item).isotope( 'reloadItems' ).isotope( { sortBy: 'original-order' } );
                updateImageLibrary(iRel);
            }
        });
    }
  }, mouseenter: function() {
    var iBox = $('#dropbox-preview');
    iBox.find('.item-checkbox').show();
  }, mouseleave: function() {
    var iBox = $('#dropbox-preview');
    iBox.find('.item-checkbox').hide();
    var checkbox = iBox.find('input[type="checkbox"]');
    if(checkbox.prop('checked')){
        iBox.find('.item-checkbox').show();
    } else {
        iBox.find('.item-checkbox').hide();
    }
  }
},'.browser-btn');

var iLibraryPreview = $('.images-library-preview');
$.each(iLibraryPreview, function(){
    var iCheckbox = $(this).find('input[type="checkbox"]');
    if(iCheckbox.length == 0) {
        $(this).hide();
        var iParent = $(this).parents('.images-library-wrapper');
        var iPanel = iParent.siblings('.images-library-panel');
        iPanel.find('a[action="select-all"]').hide();
        iPanel.find('a[action="delete"]').hide();
    } else {
        var iParent = $(this).parents('.images-library-wrapper');
        var iPanel = iParent.siblings('.images-library-panel');
        iPanel.find('a[action="select-all"]').show();
        iPanel.find('a[action="delete"]').show();
        $(this).show();
    }
});

$(".fancybox, .fancybox-image").fancybox({
    openEffect  : 'elastic',
    closeEffect : 'elastic',

    helpers : {
        title : {
            type : 'inside'
        }
    }
});

$('[action="browser-images"').click(function(){
    //$.openWindow( $(this).attr('href'), 'Images Browser', '1000','500'); 
    var iTarget = $('#'+ $(this).attr('data-target'));
    var iWidth = $(window).width() - 50;
    var iHeight = $(window).height() - 50;

    $.fancybox.open({
        href: $(this).attr('href'),
        type: 'iframe',
        padding:0,
        margin:0,
        maxWidth    : iWidth,
        maxHeight   : iHeight,
        fitToView   : true,
        width       : iWidth,
        height      : iHeight,
        autoSize    : false,
        closeClick  : false,
        openEffect  : 'none',
        closeEffect : 'none',
        autoScale: true,
        centerOnScroll: true,
        autoCenter: true,
        beforeShow  : function () {
            $('.fancybox-margin').css('margin-right','0px');
            $('.fancybox-wrap').addClass('fancybox-browser-images');
        },
        afterLoad : function() {
            var iFrame = $('.fancybox-iframe').contents();
            iFrame.find('body').css({
                width : (iWidth - 5) + 'px',
                height : iHeight + 'px'
            });
            iFrame.find('.row').css('height', (iHeight-80) + 'px');
        },
        beforeClose: function() {
            var iFrame = $('.fancybox-iframe').contents();
            iFrame.find('.image-thumb').each(function(){
                if($(this).hasClass('checked')){
                    var iName = $(this).attr('data-name');
                    var iPath = $(this).attr('data-path');
                    var iThumb = $(this).find('img').attr('src');
                    var iLarge = $(this).find('a[action="zoom"]').attr('href');

                    iTarget.find('.image-thumb').attr('data-name', iName);
                    iTarget.find('.image-thumb').attr('data-path', iPath);
                    iTarget.find('span.filename').html(iName);
                    iTarget.find('input').attr('value', iPath);
                    iTarget.find('img').attr('src', iThumb);
                    iTarget.find('a[action="zoom"]').attr('href', iLarge);

                    $('[action="info-image"]').removeClass('hidden');
                }
            });
        }
    });

    return false;
});

$('[action="info-image"]').click(function(){
    var iTarget = $('#'+ $(this).attr('data-target'));
    var iName = iTarget.find('.image-thumb').attr('data-path');
    var iURL = $(this).attr('href');
    var iHeight = $(window).height() - 50;

    $('#modal-dialog').modal('show', {backdrop: 'static'});
    
    $.ajax({
        url: iURL + iName,
        success: function(response)
        {
            $('#modal-dialog .modal-header').hide();
            $('#modal-dialog .modal-body').html(response).addClass('clear-padding').css({
                height : iHeight,
                overflow:'hidden'
            });
            $('#modal-dialog .modal-footer').hide();
        }
    });
});

$('[action="remove-image"]').click(function(){
    var iTarget = $('#'+ $(this).attr('data-target'));

    $('[action="info-image"]').addClass('hidden');

    iTarget.find('.image-thumb').attr('data-name', '');
    iTarget.find('.image-thumb').attr('data-path', '');
    iTarget.find('span.filename').html('');
    iTarget.find('input').attr('value', '');
    iTarget.find('img').attr('src', '');
    iTarget.find('a[action="zoom"]').attr('href', '');
});

function updateImageLibrary(iRel) {
    var iData = new Array();
    var iLibrary = $('#'+iRel+'-preview');
    var iCheckbox = iLibrary.find('input[type="checkbox"]');

    //console.log(iCheckbox);
    console.log('iCheckboxCount: '+ iCheckbox.length);
    //console.log('iCheckbox: '+ iCheckbox);

    $.each(iCheckbox, function(){
        iData.push($(this).val());
    });
    //console.log(iData);
    //console.log('iDataArray: '+ iData);
    iData = JSON.stringify(iData);
    //console.log('iDataStringify: '+ iData);
    iData = iData.replace(/"/gi,"'");
    //console.log('iDataReplace: '+ iData);
    $('#'+iRel+'-input').val(iData);
}

function selectCode(id) {
    if (document.selection) {
        var div = document.body.createTextRange();
        div.moveToElementText(document.getElementById(id));
        div.select();
    }
    else {
        var div = document.createRange();
        div.setStartBefore(document.getElementById(id));
        div.setEndAfter(document.getElementById(id));
        window.getSelection().addRange(div);
    }
}

//tag input
function onAddTag(tag) {
    alert("Added a tag: " + tag);
}
function onRemoveTag(tag) {
    alert("Removed a tag: " + tag);
}

function onChangeTag(input,tag) {
    alert("Changed a tag: " + tag);
}

$(function() {

    $('#tags_1').tagsInput({width:'auto'});
    $('#tags_2').tagsInput({
        width: '250',
        onChange: function(elem, elem_tags)
        {
            var languages = ['php','ruby','javascript'];
            $('.tag', elem_tags).each(function()
            {
                if($(this).text().search(new RegExp('\\b(' + languages.join('|') + ')\\b')) >= 0)
                    $(this).css('background-color', 'yellow');
            });
        }
    });

    // Uncomment this line to see the callback functions in action
    //			$('input.tags').tagsInput({onAddTag:onAddTag,onRemoveTag:onRemoveTag,onChange: onChangeTag});

    // Uncomment this line to see an input with no interface for adding new tags.
    //			$('input.tags').tagsInput({interactive:false});
        // validate form on keyup and submit
});