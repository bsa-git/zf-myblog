<div class="box3 menu-list">
    <h3>{'Администрирование'|translate}</h3>
    <ul style="list-style-type: none;">
        {if ($isAdmin)}
        <li>
            <a href="{'/user'|url}"><i class="icon-user icon-white"></i> {'Пользователи'|translate}</a>
        </li>
        {/if}
        <li>
            <a href="{'/blog'|url}" ><i class="icon-comment icon-white"></i> {'Блоги'|translate}</a>
        </li>
        {if ($isAdmin)}
        <li>
            <a href="{'/config'|url}" ><i class="icon-cog icon-white"></i> {'Конфигурирование'|translate}</a>
        </li>
        {/if}
        {if ($isAdmin)}
        <li>
            <a href="{'/tools'|url}" ><i class="icon-wrench icon-white"></i> {'Инструменты'|translate}</a>
        </li>
        {/if}
    </ul>
    
</div>
