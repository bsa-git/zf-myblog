{include file='header.tpl' 
    section='config'
    leftcolumn='index/lib/left-column.tpl'
}

<dl>
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
</dl>
{include file='footer.tpl'}