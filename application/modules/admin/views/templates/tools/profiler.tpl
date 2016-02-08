{include file='header.tpl' section='tools'
    accordion=true 
    dialog=true 
    sections=true 
    windows=true
    leftcolumn='tools/lib/left-column.tpl'
}
{*Вывод кнопок для получения отчетов*}
{include file='lib/report-buttons.tpl'
    pdf_visible=false
    report_visible=false
    win_visible=false
    infowin_id='request-cycle-in-zf'
}

{* Данные/Сообщения для передачи в JavaScript при работе с Tools *}
{include file='lib/msg-box/tools.tpl'}

{*Загрузка скриптов класса BlogView*}
<script type="text/javascript" src="{'/js/BlogView.class.js'|urlres}"></script>
{* Запомнить параметры скриптов в списке параметров *}
{literal}
<script type="text/javascript">
        addScriptParams('BlogView', {});
</script>
{/literal}

{* Контейнера для окна инф. помощи *}
{if $resultsProfiler}
    {* Аккордион *}
    <ul class="accordion" id="accordion">
        {foreach name=results item=result key=k from=$resultsProfiler}
            <li class="section">
                <a href="{$smarty.foreach.results.index}" class="title">
                    <img src="{"/images/system/Profiler.png"|urlres}"/> {$k}
                </a>
                <div class="toggle">
                    <div class="panel-row-yellow">
                        {$result}
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
{/if}

{literal}
<script type="text/javascript">
    // Добавим параметры в список параметров для аккордиона
    addScriptParams('AccordionBox', {
        id: 'accordion',
        options : { mutuallyExclusive: false  },
        states: {disable: [], show: []}
    });
</script>
{/literal}

{include file='footer.tpl'}