{include file='header.tpl' 
    section='user'
    leftcolumn='index/lib/left-column.tpl'
}

<dl>
    <dt class="dt-list">
        <i class="fa fa-users fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_user.gif'|urlres}" /><![endif]--> {'Управление пользователями сайта'|translate}
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
</dl>
{include file='footer.tpl'}