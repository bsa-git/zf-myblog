{* Cоздание заголовков таблицы *}
<thead>

{if $is_group_head == 1}
    <tr class='h'>
        {foreach from=$column_model item=column}
            {if $column.parent == ''}  
                <th valign="bottom" rowspan="2" width="{$column.width}px">{$column.title}</th>
            {else}
                {if $name_group !== $column.parent}
                    {assign var=name_group value=$column.parent}
                    <th colspan="{$column.count_childrens}" width="{$column.width}px">{$column.parent}</th>
                {/if}
                
            {/if}
            
        {/foreach}
    </tr>
    <tr class='h'>
        {foreach from=$column_model item=column}
            {if $column.parent} 
                <th width="{$column.width}px">{$column.title}</th>
            {/if}
    {/foreach}
    </tr>
        
{else}
    <tr class='h'>
    {foreach from=$column_model item=column}
        <th width="{$column.width}px">{$column.title}</th>
    {/foreach}
    </tr>
{/if}
</thead>