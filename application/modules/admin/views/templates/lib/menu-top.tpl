<!-- Menu -->
<div class="container">
    <header>
        <nav>
            <ul id="menu">
                <li{if $section == 'home'} id="menu_active"{/if}>
                    <a href="{'/index'|url}"><i class="icon-home icon-white"></i> {'Главная'|translate}</a>
                </li>
                {if ($isAdmin)}
                    <li{if $section == 'user'} id="menu_active"{/if}>
                        <a href="{'/user'|url}" style="width: 150px;"><i class="icon-user icon-white"></i> {'Пользователи'|translate}</a>
                    </li>
                {/if}
                {if ($isAdmin)||($isEditor)}
                    <li{if $section == 'blog'} id="menu_active"{/if}>
                        <a href="{'/blog'|url}" style="width: 100px;"><i class="icon-comment icon-white"></i> {'Блоги'|translate}</a>
                    </li>
                {/if}
                {if ($isAdmin)}
                    <li{if $section == 'config'} id="menu_active"{/if}>
                        <a href="{'/config'|url}" style="width: 200px;"><i class="icon-cog icon-white"></i> {'Конфигурирование'|translate}</a>
                    </li>
                {/if}
                {if ($isAdmin)}
                    <li{if $section == 'tools'} id="menu_active"{/if}>
                        <a href="{'/tools'|url}" style="width: 150px;"><i class="icon-wrench icon-white"></i> {'Инструменты'|translate}</a>
                    </li>
                {/if}
                <li><a href="{'/index/out'|url}" style="width: 100px;"><i class="icon-share icon-white"></i> {'Выход'|translate}</a></li>
            </ul>
        </nav>
    </header>
</div>