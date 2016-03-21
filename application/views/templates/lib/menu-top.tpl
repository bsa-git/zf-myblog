<!-- Menu -->
<div class="container">
    <header>
        <nav>
            <ul id="menu">
                <li{if $section == 'home'} id="menu_active"{/if}>
                    <a href="{'/index'|url}"><i class="icon-home icon-white"></i> {'Главная'|translate}</a>
                </li>
                {if $authenticated}
                    <li{if $section == 'account'} id="menu_active"{/if}>
                        <a href="{'/account'|url}"><i class="icon-user icon-white"></i> {'Ваш профиль'|translate}</a>
                    </li>
                    {if (!$isCommentator)}
                        <li{if $section == 'blogmanager'} id="menu_active"{/if}>
                            <a href="{'/blogmanager'|url}"><i class="icon-comment icon-white"></i> {'Ваш блог'|translate}</a>
                        </li>
                    {/if}
                    {if ($isAdmin)||($isEditor)}
                        <li{if $section == 'modules'} id="menu_active"{/if}>
                            <a href="{'/index/modules'|url}"><i class="icon-th-list icon-white"></i> {'Модули'|translate}</a>
                        </li>
                    {/if}
                    <li><a href="{'/account/logout'|url}"><i class="icon-share icon-white"></i> {'Выход'|translate}</a></li>
                {else}
                    <li{if $section == 'register'} id="menu_active"{/if}>
                        <a href="{'/account/register'|url}"><i class="icon-edit icon-white"></i> {'Регистрация'|translate}</a>
                    </li>
                    <li{if $section == 'login'} id="menu_active"{/if}>
                        <a href="{'/account/login'|url}"><i class="icon-hand-right icon-white"></i> {'Вход'|translate}</a>
                    </li>
                {/if}
            </ul>
        </nav>
    </header>
</div>
