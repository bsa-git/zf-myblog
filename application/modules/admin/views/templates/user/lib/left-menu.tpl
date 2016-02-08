<div class="box3 menu-list">
    <h3>{'Пользователи'|translate}</h3>
    <ul style="list-style-type: none;">
        {if ($isAdmin)}
        <li>
            <a href="{'/user/users'|url}"><i class="icon-user icon-white"></i> {'Список пользователей'|translate}</a>
        </li>
        {/if}
        {if ($isAdmin)}
        <li>
            <a href="{'/user/news'|url}"><i class="icon-envelope icon-white"></i> {'Рассылка новостей'|translate}</a>
        </li>
        {/if}
    </ul>
    
</div>
