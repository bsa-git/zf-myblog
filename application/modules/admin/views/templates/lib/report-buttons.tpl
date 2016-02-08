<div class="report-buttons">
    {if $pdf_visible}
        <a href="{$url_pdf|url}?name={$name_pdf}" id="get-report-pdf" title="{'Отчет в PDF формате'|translate}" ><i class="fa fa-file-text-o fa-2x"></i><!--[if IE 7]><img id="get-report-pdf" class="report-button" src="{'/images/system/pdf.gif'|urlres}"  alt=""/><![endif]--></a>
    {/if}
    {if $report_visible}
        <a href="{$url_content|url}?report=true" target="_blank" title="{'Версия для печати'|translate}" ><i class="fa fa-print fa-2x"></i><!--[if IE 7]><img id="content-in-new-tab" class="report-button" src="{'/images/system/print16x16.png'|urlres}" /><![endif]--></a>
    {/if}
    {if $win_visible}
        <a href="#" id="content-in-new-window" title="{'Показать в отдельном окне'|translate}" ><i class="fa fa-list-alt fa-2x"></i><!--[if IE 7]><img class="report-button" src="{'/images/system/win2.gif'|urlres}" /><![endif]--></a>
    {/if}
    {if $infowin_id}
        <a href="#" id="{$infowin_id}" class="report-button help-info-win" title="{'Помощь'|translate}" ><i class="fa fa-question-circle fa-2x"></i><!--[if IE 7]><img class="report-button" src="{'/images/system/help24x24.png'|urlres}" /><![endif]--></a>
    {/if}
    <br /><br />
</div>