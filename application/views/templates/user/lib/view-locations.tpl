{* Загрузим гео координаты *}
<script type="text/javascript" src="{'/js/BlogLocations.class.js'|urlres}"></script>

{* Запомним параметры для обьекта геокоординат на карте *}
<script type="text/javascript">
    addScriptParams('BlogLocations', {ldelim}   
        container: 'post-locations',
        user_id: {$user->getId()},
        post_id: {$post->getId()},
        accordion: {ldelim}id: 'accordion', section: 4, show: false{rdelim}
    {rdelim});
</script>
<li class="section">
    <a href="4" class="title">
        <img src="{'/images/system/Weblink.png'|urlres}"/> 
        {"Географические координаты"|translate} ({$post->locations|@count})
    </a>
    <div class="toggle">
        <div class="accordion-toggle-container" style="display:none">
            <fieldset id="post-locations" class="myfrm">
                <legend>{'Карта'|translate}</legend>
                <h2>{'Координаты'|translate}</h2>
                <ul>
                        {foreach from=$post->locations item=location}
                            <li>
                                <a  class="geo" href="" location_id="{$location->getId()}"
                                    title="{$location->latitude};{$location->longitude}">
                                            {$location->description|escape}
                                </a>
                            </li>
                        {/foreach}
                </ul><br />
                <div id="map-locations" class="map"></div>
            </fieldset>
        </div>
    </div>
</li>