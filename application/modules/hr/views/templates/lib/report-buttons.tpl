<div class="report-buttons">
    {if $pdf_visible}
        <a href="{$url_pdf|url}?name={$name_pdf}" ><img id="get-report-pdf" class="report-button" src="{'/images/system/pdf.gif'|urlres}" title="{'Отчет в PDF формате'|translate}" alt=""/></a>
    {/if}
    {if $report_visible}
        <a href="{$url_content|url}?report=true" target="_blank" ><img id="content-in-new-tab" class="report-button" src="{'/images/system/print16x16.png'|urlres}" title="{'Версия для печати'|translate}" alt=""/></a>
    {/if}
    {if $win_visible}
        <input type="image" id="content-in-new-window" class="report-button" src="{'/images/system/win2.gif'|urlres}" alt title="{'Показать в отдельном окне'|translate}"  />
    {/if}
    {if $infowin_id}
        <input type="image" id="{$infowin_id}" class="report-button help-info-win" src="{'/images/system/help24x24.png'|urlres}" alt title="{'Помощь'|translate}"  />
    {/if}
</div>