{include file='header.tpl' 
    section='tools' 
    dialog=true
    leftcolumn='tools/lib/left-column.tpl'
}

<script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>
{* Запомнить параметры скриптов в списке параметров *}
{literal}
<script type="text/javascript">
    var msg = $('msgIsPreparingReport').innerHTML;
    addScriptParams('BlogView', {
        containers: ['report-content']
//        dialog_info: {
//            type: 'ZendProgress',
//            msg: msg, 
//            //width: 350,
//            //height: 120,
//            open: false,
//            iframe_src:  '/admin/tools/pdf?name=zend-progress'
//            //cancel: false,
//            //url_cancel: '/admin/tools/phpinfo'
//        }
    });
</script>
{/literal}
<a href="{$urlFilePDF}">Ваш отчет в формате PDF</a>
{include file='footer.tpl'}