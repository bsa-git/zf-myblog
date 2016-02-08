{* Cоздание тела таблицы *}
<tbody>
    {foreach name=outer item=row from=$rows_body}
        {if $smarty.foreach.outer.index % 2 == 0}
            <tr class='row-even'>
        {else}
            <tr class='row-odd'>
        {/if}
        {foreach key=_key item=_value from=$column_model name=iner}
            {if $smarty.foreach.iner.first && $_value.type == 'string'}
                <td class="e" width="{$_value.width}px">{$row.$_key}</td>
            {else}
                {if $_value.type == 'boolean'}<td class="v" width="{$_value.width}px">{$row.$_key}</td>{/if}
                {if $_value.type == 'number'}<td class="vr" width="{$_value.width}px">{$row.$_key}</td>{/if}
                {if $_value.type == 'string'}<td class="vl" width="{$_value.width}px">{$row.$_key}</td>{/if}
            {/if}
        {/foreach} 
        </tr>
    {/foreach}
</tbody>