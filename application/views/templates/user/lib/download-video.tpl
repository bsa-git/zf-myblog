{foreach from=$post->video item=video name=videos}
    {* Определим класс видео *}
    {assign var=fileImage value=$video->type}
    
    
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
    
    {* Определим является ли ресурс URL *}
    {assign var=arrRes value=$video->type|split:'-'}
    {if $arrRes[0] == 'url' }
        {assign var=isUrl value=true}
    {else}
        {assign var=isUrl value=false}
    {/if}
    
    {* Определим ссылку на видио *}
    {if $isUrl}
        {assign var=hrefVideo value=$video->identifier|escape}
    {else}
        {assign var=hrefVideo value=$video->getFullUrl_Res($username)}
    {/if}
    
    {* Определим класс видио проигрывателя *}
    {if $video->type == 'url-youtube'}
        {assign var=classPlayer value='proto-tube'}
    {elseif $video->type == 'url-wmv' || $video->type == 'file-wmv'}
        {assign var=classPlayer value='windows-player'}
    {else}
        {assign var=classPlayer value='flow-player'}
    {/if}
    
    {* Определим класс видио проигрывателя *}
    {assign var=classExtension value=$arrRes[1]}
    
    {if !($classExtension == 'rtmp' || $classExtension == 'pseudostreaming' || $classExtension == 'httpstreaming')}
        <div class="video-row" id="video_{$video->getId()}">
            <div class="player-close text-right" style="display: none">
                <a href="" title="{'Закрыть'|translate}">
                    <img src="{'/images/system/delete_button.gif'|urlres}">
                </a>
            </div>
            <a class="{$classPlayer} {$classExtension}  player" style="background-image:url({"/images/media/128x128/$fileImage.png"|urlres});" href="{$hrefVideo}">
                <img src="{'/images/media/play.png'|urlres}">
            </a>
            <div class="video-info">
                <h3 class="video-title">{$titleVideo}</h3>
                <p>{$commentVideo}</p>
            </div>
            {if $isCompatibleBrowser}
                <br clear="all">
            {/if}
        </div>
    {/if}
{/foreach}