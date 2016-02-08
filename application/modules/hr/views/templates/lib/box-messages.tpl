{if $messages|@count > 0}
    <div id="messages" class="box">
        {if $messages|@count == 1}
            <strong>{'Состояние сообщения'|translate}:</strong>
            {$messages.0|escape}
        {else}
            <strong>{'Состояние сообщения'|translate}:</strong>
            <ul>
                {foreach from=$messages item=row}
                    <li>{$row|escape}</li>
                {/foreach}
            </ul>
        {/if}
    </div>
{else}
    <div id="messages" class="box" style="display:none"></div>
{/if}