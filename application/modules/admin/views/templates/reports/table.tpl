{* Cоздание таблицы *}
<div class="report-content" >
    {* Cоздание титла таблицы *}
    {if $is_row_header}
        {include file='reports/table_title.tpl'}<br />
    {/if}
    {* Cоздание самой таблицы *}
    <div class="table-container" style="max-width: 100%;" >
    <table border="0" cellpadding="3" width="100%">
        {* Cоздание названий столбцов таблицы *}
        {include file='reports/table_header.tpl'}
        {* Cоздание нижнего колонтитула таблицы *}
        {include file='reports/table_footer.tpl'}
        {* Cоздание тела таблицы *}
        {include file='reports/table_body.tpl'}
    </table>
    </div>
</div>
