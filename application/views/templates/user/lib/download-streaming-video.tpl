{* Определим индекс видио *}
{assign var="indexVideo" value=0}
{foreach from=$post->video item=video}
    
    {* Определим название видио *}
    {if $video->name|escape }
        {assign var=titleVideo value=$video->name|escape}
    {else}
        {assign var=titleVideo value=$video->identifier|escape}
    {/if}
    
    {* Определим комментарий к видио *}
    {if $video->comment|escape }
        {assign var=commentVideo value=$video->comment|escape|nl2br}{* nl2br -> Превращает каждый перевод строки в тэг <br /> в указанной переменной*}
    {else}
        {assign var=commentVideo value='Комментарий отсутствует...'|translate}
    {/if}
   
    {* Определим класс видио проигрывателя *}
    {assign var=arrRes value=$video->type|split:'-'}
    {assign var=classExtension value=$arrRes[1]}
    
    {if $classExtension == 'rtmp' || $classExtension == 'pseudostreaming' || $classExtension == 'httpstreaming'}
        <li>
            <div class="streaming-video-item" style="float: left; margin-right: 10px"></div>
            <a  href="{$indexVideo}">{$titleVideo}</a>
        </li>
        {math equation="x + 1" x=$indexVideo assign="indexVideo"}
    {/if}
{/foreach}
