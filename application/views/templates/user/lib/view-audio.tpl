{* Загрузим Аудио в сообщении *}
<script type="text/javascript" src="{'/js/BlogViewAudio.class.js'|urlres}"></script>        

{* Создадим обьект проигрывателя Аудио *}
<script type="text/javascript">//checked="checked"
    addScriptParams('BlogViewAudio', {ldelim}
        container: 'audio-container',//'audio-pimp3',
        options: {ldelim} 
            Url:'/user/{$user->username}/post/{$post->getId()}/audios',
            Autoplay: true
        {rdelim},
        accordion: {ldelim}id: 'accordion', section: 1, show: false{rdelim}
    {rdelim});
</script>
<li class="section" >
    <a href="1" class="title">
        <img src="{'/images/system/music18x18.png'|urlres}"/> 
        {"Аудио"|translate} ({$post->audio|@count})
    </a>
    <div class="toggle">
        <div class="accordion-toggle-container" style="display:none">
            {* Аудио для сообщения *}
            <div id="post-audio">
                <div class="video-row">
                    <div class="player-close text-right" style="display: block">
                        <a href="" title="{'Закрыть'|translate}">
                            <img src="{'/images/system/delete_button.gif'|urlres}">
                        </a>
                    </div>
                    <center>
                        <div id="audio-container" style="width:425px;height:35px;margin-top:20px;"></div>
                    </center>
                    <h2>{'Список аудио файлов'|translate}</h2>

                    <ul id="audio_list">
                        {foreach from=$post->audio item=audio name=audios}
                        <li id="audio_{$smarty.foreach.audios.index}">
                            <div class="audio-item" style="float: left; margin-right: 10px"></div>
                            <a  href="{$audio->getFullUrl_Res($user->username)}">
                                {$audio->filename|escape} 
                                {if ($audio->comment)}
                                    ({$audio->comment|escape|nl2br}){* nl2br -> Превращает каждый перевод строки в тэг <br /> в указанной переменной*}
                                {/if}
                            </a>
                        </li>
                        {/foreach}
                    </ul>
                    <br />
                </div>
                <br />
                <center>
                    <div class="flashplayer-download">
                        <i>{'player_audio_files_is_not_installed'|translate}</i> <a href="http://get.adobe.com/flashplayer">http://get.adobe.com/flashplayer</a>
                    </div>
                </center>
            </div>
        </div>
    </div>
</li>