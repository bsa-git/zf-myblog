{include file='header.tpl' 
    section='tools' 
    buttons=true 
    windows=true 
    dialog=true
    leftcolumn='tools/lib/left-column.tpl'
}
{*Вывод кнопок для получения отчетов*}
{include file='lib/report-buttons.tpl'
    pdf_visible=false
    report_visible=false
    win_visible=false
    infowin_id='the-implementation-of-site-search'
}

{* Данные/Сообщения для передачи в JavaScript при работе с Tools *}
{include file='lib/msg-box/tools.tpl'}

{*Загрузка скриптов класса AdminTools*}
<script type="text/javascript" src="{'/js/AdminTools.class.js'|urlres}"></script>
{* Запомнить параметры скриптов в списке параметров *}
{literal}
<script type="text/javascript">
        addScriptParams('AdminTools', {
        container: ''
    });
</script>
{/literal}
{*Загрузка скриптов класса BlogView*}
<script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>
{* Запомнить параметры скриптов в списке параметров *}
{literal}
<script type="text/javascript">
        addScriptParams('BlogView', {});
</script>
{/literal}
<fieldset id="preview-tags" class="myfrm">
    <legend>{'Поисковый индекс для сообщений'|translate}</legend>
    <br />    
    <a href="{'/default/search/rebuild'|url}" class="toolbar-button search_lucene-index" style="width: 132px;" title="{'Восстановление поискового индекса'|translate}">
        <span class="icon search-index-button">&nbsp;</span>
        <span class="text">{'Восстановить'|translate}&nbsp;</span>
    </a> {'Восстановление поискового индекса для сообщений'|translate} <span class="tooltip ajax_rebuild-the-search-index">{'подробнее...'|translate}</span><br /><br />
    <a href="{'/default/search/optimize'|url}" class="toolbar-button search_lucene-index" style="width: 132px;" title="{'Оптимизация поискового индекса'|translate}">
        <span class="icon search-index-button">&nbsp;</span>
        <span class="text">{'Оптимизировать'|translate}&nbsp;</span>
    </a> {'Оптимизация поискового индекса для сообщений'|translate} <span class="tooltip ajax_optimization-of-the-search-index">{'подробнее...'|translate}</span><br /><br />
</fieldset> 

<br />
<br />

{include file='footer.tpl'}