{*Определим наличие сообщения*}
{if $message|@is_array || $message|strlen > 0}
    {assign var=hasMessage value=true}
    {*Установим класс по умолчанию*}
    {if $class_message|strlen == 0}
        {assign var=class_message value=message}
    {/if}
{else}
    {assign var=hasMessage value=false}
{/if}
{*Сообщения для javascript*}
<div class="msg-box" style="display: none">
    <p id="msg-error">{'error'|translate}</p>
    <p id="msg-warning">{'warning'|translate}</p>
    <p id="msg-information">{'information'|translate}</p>
    <p id="msg-caution">{'caution'|translate}</p>
    <p id="msg-message">{'message'|translate}</p>
</div>
{*Выведем сообщение*}
<div class="system-message" id="container-message" {if !$hasMessage} style="display:none"{/if}>
    
    {if $class_message == 'blockquote'}
        <blockquote id="message-items">
            {if $message|@is_array}
                <ul>
                {foreach from=$message item=str}
                    <li>{$str|escape}</li>
                {/foreach}
                </ul>
            {else}
                <p>
                    {$message|escape}
                </p>
            {/if}
       </blockquote>
    {else}
        <div class="{$class_message}" id="message-items">
            <a id="close-message-items"  href="" title="{"Закрыть сообщение"|translate}">
                <img src="{'/images/system/delete_button.gif'|urlres}"> 
            </a>
            <b class="{$class_message}">{$class_message|translate}</b>
            <p>
            {if $message|@is_array}
                {foreach from=$message item=str}
                    {$str}<br />
                {/foreach}
            {else}
                {$message}
            {/if}
            </p>
        </div>
    {/if}
</div>