{* Cоздание нижнего колонтитула таблицы *}
<tfoot>
    {foreach name=outer item=row from=$rows_footer}
        <tr>
        {foreach item=value from=$row name=iner}
            {if $smarty.foreach.iner.first}
                {if $footer_colspan > 1}
                    <td colspan="{$footer_colspan}" class="fh">{$value}</td>
                {else}
                    <td  class="fh">{$value}</td>
                {/if}
            {else}
                <td class="fv">{$value}</td>
            {/if}
        {/foreach}
        </tr>
    {/foreach}
</tfoot>