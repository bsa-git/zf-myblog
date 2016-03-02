<?php

function getToolbar($type = 'medium') {
    $toolbars = array();
    //-----------------
    $toolbars['full'] = array(
        // document
        array('Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates'),
        // clipboard
        array('Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo'),
        // editing
        array('Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt'),
        // form
        array('Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'),
        // insert
        array('Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'),
        // styles
        array('Styles', 'Format', 'Font', 'FontSize'),
        // colors
        array('TextColor', 'BGColor'),
        // basicstyles
        array('Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat'),
        // paragraph
        array('NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-
 
        ','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl'),
        // links
        array('Link', 'Unlink', 'Anchor'),
        // tools
        array('Maximize', 'ShowBlocks','-','About' ),
    );
    
    $toolbars['medium'] = array(
        // document
        array('Source', '-','Save', 'NewPage', 'Preview'),
        // clipboard
        array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'),
        // editing
        array('Find', 'Replace', '-', 'SelectAll'),
        // insert
        array('Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'),
        // styles
        array('Styles', 'Format', 'Font', 'FontSize'),
        // colors
        array('TextColor', 'BGColor'),
        // basicstyles
        array('Bold', 'Italic', 'Underline', 'Subscript', 'Superscript', '-', 'RemoveFormat'),
        // paragraph
        array('NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'),
        // links
        array('Link', 'Unlink', 'Anchor'),
        // tools
        array('Maximize', '-', 'About'),
    );
    
    $toolbars['min'] = array(
        // document
        array('Source', '-','Save','Bold', 'Italic','-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'NumberedList',  '-', 'BulletedList','Table', 'HorizontalRule', '-', 'Image', 'Link', 'Unlink','Maximize'),
        
    );
    
    return $toolbars[$type];
    
}

function smarty_function_wysiwyg($params = array(), $smarty) {
    $name = 'ckeditor_content';
    $content = '';
    $defaults = array();
    //--------------------------

    //------ инициализация опций ----------

    // Установим инструментальную панель
    $defaults['type_toolbar'] = 'medium';

    // Установим парметры подключения файлового менеджера
    $defaults['filebrowserBrowseUrl'] = Default_Plugin_SysBox::getUrlRes('/js/kcfinder/browse.php?type=files');
    $defaults['filebrowserImageBrowseUrl'] = Default_Plugin_SysBox::getUrlRes('/js/kcfinder/browse.php?type=images');
    $defaults['filebrowserFlashBrowseUrl'] = Default_Plugin_SysBox::getUrlRes('/js/kcfinder/browse.php?type=flash');
    $defaults['filebrowserUploadUrl'] = Default_Plugin_SysBox::getUrlRes('/js/kcfinder/upload.php?type=files');
    $defaults['filebrowserImageUploadUrl'] = Default_Plugin_SysBox::getUrlRes('/js/kcfinder/upload.php?type=images');
    $defaults['filebrowserFlashUploadUrl'] = Default_Plugin_SysBox::getUrlRes('/js/kcfinder/upload.php?type=flash');

    // Установим язык интерфейса
    $defaults['language'] = Default_Plugin_SysBox::getTranslateLocale();

    // Установим высоту окна редактора
    $defaults['height'] = 300;

    foreach ($defaults as $k => $v) {
        $params[$k] = array_key_exists($k, $params) ? $params[$k] : $v;
    }

    $params['toolbar'] = getToolbar($params['type_toolbar']);
    
    if (isset($params['name']))
        $name = $params['name'];

    if (isset($params['content']))
        $content = $params['content'];


    $basePath = Default_Plugin_SysBox::getUrlRes('/js/ckeditor/') . '/';

    //------------- создание редактора --------------
    // При создании редактоа он скрывает textarea
    // но создает новый конпонент textarea с атрибутами в свойстве - textareaAttributes
    $ckeditor = new Default_Plugin_CkEditor($basePath);

    // Скроем вновь созданный элемент textarea
    $ckeditor->textareaAttributes = array("style" => "visibility: hidden; display: none;");

    $ckeditor->editor($name, $content, $params);

}