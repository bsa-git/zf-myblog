{* Определим наличие изображений в сообщении *}
<script type="text/javascript">
    addScriptParams('CarouselView', {ldelim}
        images: 'post-images', 
        carousel: 'carousel',
        images_count: {$post->images|@count},
        //ajax: {ldelim} url:'/user/{$user->username}/post/{$post->getId()}/images', elementSize: 150{rdelim},
        accordion: {ldelim}id: 'accordion', section: 0, show: false{rdelim}
    {rdelim});
</script>
<li class="section" >
    <a href="0" class="title">
        <img src="{'/images/system/views16x16.png'|urlres}"/> 
        {"Изображения"|translate} ({$post->images|@count})
    </a>
    <div class="toggle">
        <div class="accordion-toggle-container" style="display:none">
            {* Изображения для сообщения *}
            <fieldset id="post-images" class="myfrm" style="display: none; background-color: #fff">
                <legend>{'Фотографии'|translate}</legend>
                <div id="carousel" class="horizontal_carousel" >
                    <div class="previous_button"></div>
                    <div class="carousel-container">
                        <ul>
                            {foreach from=$post->images key=k item=image}
                                <li>
                                    <a href="{$image->createThumbnail(600, 0, $user->username)}" rel="lightbox[blog]" title="{$image->comment|escape}">
                                        <img src="{$image->createThumbnail(120, 0, $user->username)}" title="{$image->comment|escape}" alt=""  />
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                        <div id="spinner-carousel" class="spinner" style="display: none;"><img src="{'/images/system/progress_bar.gif'|urlres}" title="" alt=""  /></div>
                    </div>
                    <div class="next_button"></div>
                </div>
                <div style="clear:both"><br /></div>
            </fieldset>
        </div>
    </div>
</li>