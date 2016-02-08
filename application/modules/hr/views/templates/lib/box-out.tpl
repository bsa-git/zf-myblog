<div class="box">
    <em>{if $isAdmin}
	{'Администратор'|translate}
    {elseif $isEditor}
        {'Редактор'|translate}
    {else}
        {'Пользователь'|translate}
    {/if}</em>
    {$identity->first_name|escape} {$identity->last_name|escape}
    (<a href="{'/index/out'|url}">{'Выход'|translate}</a>).
</div>
    
