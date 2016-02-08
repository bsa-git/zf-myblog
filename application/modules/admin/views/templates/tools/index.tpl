{include file='header.tpl' 
    section='tools'
    leftcolumn='index/lib/left-column.tpl'
}

<dl>
    <dt class="dt-list">
        <i class="fa fa-wrench fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_tools.gif'|urlres}" /><![endif]--> {'Список задач'|translate}
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
{include file='footer.tpl'}