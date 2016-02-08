{include file='header.tpl' 
    section='tools' 
    phpinfo=true 
    windows=true 
    dialog=true 
    leftcolumn='tools/lib/left-column.tpl'
}
{*Вывод кнопок для получения отчетов*}
{include file='lib/report-buttons.tpl'
    pdf_visible=false
    report_visible=true
    win_visible=true
    infowin_id='the-current-info-of-php'
}

<script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>
{* Запомнить параметры скриптов в списке параметров *}
{literal}
<script type="text/javascript">
    var msg = $('msgIsPreparingReport').innerHTML;
    addScriptParams('BlogView', {
        containers: ['report-content'],
        dialog_info: {
            type: 'WaiteServerAction',
            msg: msg 
            //width: 350,
            //height: 120,
            //open: false,
            //cancel: false,
            //url_cancel: '/admin/tools/phpinfo'
        }
    }
    );
</script>
{/literal}

<div id="report-content" >
    {info_php}
</div>

{include file='footer.tpl'}