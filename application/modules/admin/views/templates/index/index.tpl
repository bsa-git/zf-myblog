{include file='header.tpl' 
    section='home' 
    leftcolumn='index/lib/left-column.tpl'
}

<p class="lead">{'Модуль предназначен для администрирования сайта.<br />В него входят следующие разделы'|translate}:</p>

<dl>
    <dt class="dt-list">
        <a href="{'/user'|url}"><i class="fa fa-users fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_user.gif'|urlres}" /><![endif]-->{'Управление пользователями сайта'|translate}</a>
    </dt>
    <dd class="dd-list">
        <ul style="list-style-type: none;">
            <li>
                <a href="{'/user/users'|url}"><i class="fa fa-user fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/users.png'|urlres}" /><![endif]-->{'Список пользователей'|translate}</a>
            </li>
            <li>
                <a href="{'/user/news'|url}"><i class="fa fa-rss-square fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/subscribe.gif'|urlres}" /><![endif]-->{'Рассылка новостей'|translate}</a>
            </li>
        </ul>
    </dd>
    <dt class="dt-list">
        <a href="{'/blog'|url}"><i class="fa fa-comments fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_content.gif'|urlres}" /><![endif]-->{'Управление блогом'|translate}</a>
    </dt>
    <dd class="dd-list">
        <ul style="list-style-type: none;">
            <li>
                <a href="{'/blog/posts'|url}"><i class="fa fa-list fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/comp_edit.gif'|urlres}" /><![endif]-->{'Список сообщений в блогах'|translate}</a>
            </li>
        </ul>
    </dd>
    <dt class="dt-list">
        <a href="{'/config'|url}"><i class="fa fa-cog fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_settings.gif'|urlres}" /><![endif]-->{'Конфигурирование'|translate}</a>
    </dt>
    <dd class="dd-list">
        <ul style="list-style-type: none;">
            <li>
                <a href="{'/config/langs'|url}"><i class="fa fa-male fa-lg"></i><i class="fa fa-male fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/settings2.gif'|urlres}" /><![endif]-->{'Языки интерфейса'|translate}</a>
            </li>
            <li>
                <a href="{'/config/modules'|url}"><i class="fa fa-th-large fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/settings_on.gif'|urlres}" /><![endif]-->{'Настройка модулей'|translate}</a>
            </li>
            <li>
                <a href="{'/config/interface'|url}"><i class="fa fa-list-alt fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/views16x16.png'|urlres}" /><![endif]-->{'Интерфейс пользователя'|translate}</a>
            </li>
        </ul>
    </dd>
    <dt class="dt-list">
        <a href="{'/tools'|url}"><i class="fa fa-wrench fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_tools.gif'|urlres}" /><![endif]-->{'Инструменты'|translate}</a>
    </dt>
    <dd class="dd-list">
        <ul style="list-style-type: none;">
            <li>
                <a href="{'/tools/phpinfo'|url}"><i class="fa fa-code fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/settings.gif'|urlres}" /><![endif]-->{'Настройки PHP'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/listinfo'|url}"><i class="fa fa-question fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/EditTable.png'|urlres}" /><![endif]-->{'Список информационной помощи'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/search'|url}"><i class="fa fa-search fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/search.png'|urlres}" /><![endif]-->{'Поиск на сайте'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/profiler'|url}"><i class="fa fa-tachometer fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/performance.gif'|urlres}" /><![endif]-->{'Оценка быстродействия'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/backup'|url}"><i class="fa fa-download fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/database-add.png'|urlres}" /><![endif]-->{'Резервное копирование'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/loginfo'|url}"><i class="fa fa-clock-o fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/update_log.gif'|urlres}" /><![endif]-->{'Журнал событий'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/errorinfo'|url}"><i class="fa fa-bug fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/delete.gif'|urlres}" /><![endif]-->{'Журнал ошибок'|translate}</a>
            </li>
            <li>
                <a href="{'/tools/logstat'|url}"><i class="fa fa-bar-chart-o fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/logStat20x20.png'|urlres}" /><![endif]-->{'Журнал статистики'|translate}</a>
            </li>
        </ul>
    </dd>
</dl>
<br /><br />            


{include file='footer.tpl'}