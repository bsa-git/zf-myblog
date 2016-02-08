<fieldset id="preview-locations" class="myfrm" >
<legend>{'Географические координаты'|translate}</legend>
<ul class="text-center">
    {foreach from=$post->locations item=location}
        <li>{$location->description|escape}</li>
    {foreachelse}
        <li>{'Отсутствует информация о географических координатах для данного сообщения'|translate}.</li>
    {/foreach}
</ul>
<br />
<form class="text-center" method="get" action="{'/blogmanager/locations'|url}">
    <div>
        <input type="hidden" name="id" value="{$post->getId()}" />
        <input type="submit" class="btn btn-primary" value="{'Управление координатами'|translate}" />
    </div>
</form>
<br />  
</fieldset>