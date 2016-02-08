{if $audios|@count > 0}
    {foreach from=$audios item=audio}
        {* Определим название аудио *}
        {if $audio->name|escape }
            {assign var=titleAudio value=$audio->name|escape}
        {else}
            {assign var=titleAudio value=$audio->filename|escape}
        {/if}
        <li id="audio_{$audio->getId()}">
            <img src="{'images/media/thumbs/file-mp3.png'|urlres}?id={$audio->getId()}"
                alt="{$audio->name|escape}" title="{$audio->comment|escape}"/>
            {*$audio->filename|escape*}
            <div style="width: 100%; text-align: center">#{$audio->getId()}. {$titleAudio}</div>
            {*$titleAudio*}
            <form method="post" action="{'/blogmanager/audio'|url}">
                <div>
                    <input type="hidden"
                        name="id" value="{$post_id}" />
                    <input type="hidden"
                        name="image" value="{$audio->getId()}" />
                    <input type="submit" class="btn btn-mini" value="{'Удалить'|translate}" name="delete" />
                </div>
            </form>
        </li>
    {/foreach}
{/if}