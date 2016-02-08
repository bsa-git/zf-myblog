<div class="box3">
    <i class="icon-user icon-white"></i> 
    {if $authenticated}
        <em>{if $isAdmin}
            {'Администратор'|translate}
        {elseif $isEditor}
            {'Редактор'|translate}
        {else}
            {'Пользователь'|translate}
        {/if}</em>
        {*'Вы вошли как'|translate*}
        {$identity->first_name|escape} {$identity->last_name|escape}
        (<a href="{'/account/logout'|url}">{'Выход'|translate}</a>).
        <a href="{'/account/details'|url}">{'Редактировать профиль'|translate}</a>.
    {else}
        {'Вы не авторизованы на сайте'|translate}.
        <a href="{'/account/login'|url}">{'Вход'|translate}</a>  {'или'|translate} 
        <a href="{'/account/register'|url}">{'Регистрация'|translate}</a>.
    {/if}
</div>
