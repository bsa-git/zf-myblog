{* Загрузим Видео *}
<script type="text/javascript" src="{'/js/BlogViewVideo.class.js'|urlres}"></script>

{* Выведем обычное видео *}
{type_media medias=$post->video type='no_streaming' assign='count_media'}
{if $count_media > 0}
    {* Создадим обьект управления видео *}
    <script type="text/javascript">
        addScriptParams('BlogViewVideo', {ldelim}
            container: 'post-video',
            url:'/user/{$user->username}/post/{$post->getId()}/videos',
            options_youtube: {ldelim}opacity:1,showinfo:0,rel:1{rdelim},
            accordion: {ldelim}id: 'accordion', section: 2, show: false{rdelim}
        {rdelim});
    </script>
    <li class="section" >
        <a href="2" class="title">
            <img src="{'/images/system/film18x18.png'|urlres}"/> 
            {"Видео"|translate} ({$count_media})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none">
                <div id="post-video" >
                    {include file='user/lib/download-video.tpl'}
                    <br />
                    <div class="flashplayer-download">
                        <i>{'player_video_files_is_not_installed'|translate}</i> <a href="http://get.adobe.com/flashplayer">http://get.adobe.com/flashplayer</a>
                    </div>
                    <div style="clear:both"><br /></div>
                </div>
            </div>
        </div>
    </li>
{/if}
{* Выведем потоковое видео *}
{type_media medias=$post->video type='streaming' assign='count_media'}
{if $count_media > 0}
    {* Загрузим соответсвующие *}
    {*<script src="{'/js/flowplayer/flowplayer.playlist-3.2.10.min.js'|urlres}" type="text/javascript"></script>*}
    <script type="text/javascript" src="{'/js/BlogViewStreamingVideo.class.js'|urlres}"></script>        

    {* Создадим обьект управления потоковым видео *}
    <script type="text/javascript">//checked="checked"
        addScriptParams('BlogViewStreamingVideo', {ldelim}
            container: 'streaming-video-container',
            options: {ldelim} 
                Url:'/user/{$user->username}/post/{$post->getId()}/videos',
                Autoplay: false
            {rdelim},
            accordion: {ldelim}id: 'accordion', section: 3, show: false{rdelim}
        {rdelim});
    </script>
    <li class="section" >
        <a href="3" class="title">
            <img src="{'/images/system/audio2.png'|urlres}"/> 
            {"Потоковое видео/аудио"|translate} ({$count_media})
        </a>
        <div class="toggle">
            <div class="accordion-toggle-container" style="display:none">
                <div id="post-streaming-video">
                    <div class="video-row">
                        <div class="player-close text-right" style="">
                            <a href="" title="{'Закрыть'|translate}">
                                <img src="{'/images/system/delete_button.gif'|urlres}">
                            </a>
                        </div>
                        <center>
                            <div id="streaming-video-container" style="width:425px;height:300px;"></div>
                        </center>
                        <h2>{'Список потокового видео/аудио'|translate}</h2>

                        <ul id="streaming-video-list">
                            {include file='user/lib/download-streaming-video.tpl'}
                        </ul>
                        <br />
                    </div> 
                    <br />
                    <div class="flashplayer-download">
                        <i>{'player_video_files_is_not_installed'|translate}</i> <a href="http://get.adobe.com/flashplayer">http://get.adobe.com/flashplayer</a>
                    </div>
                </div>
            </div>
        </div>
    </li>
{/if}