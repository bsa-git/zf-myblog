{include file='header.tpl' 
    section='modules' 
    admin=true
    leftcolumn='index/lib/left-column.tpl'
}

<fieldset class="myfrm" >
    <legend>{'Администрирование'|translate}</legend>
    {'Модуль предназначен для администрирования сайта. <br />В него входят следующие разделы'|translate}:<br /><br />
    <ul style="list-style-type: none;">
        {if ($isAdmin)}
        <li>
            <!--[if IE 7]><a href="{'/admin/user'|url}" title="{"Управление пользователями сайта"|translate}">
                <img src="{'/images/system/title_user.gif'|urlres}"
                     alt="{"Управление пользователями сайта"|translate}" />
            </a><![endif]--> 
            <a href="{'/admin/user'|url}"><i class="fa fa-users fa-2x"></i> {'Управление пользователями сайта'|translate}</a><br /><br />
        </li>
        {/if}
        <li>
            <!--[if IE 7]><a href="{'/admin/blog'|url}" title="{"Управление блогом"|translate}">
                <img src="{'/images/system/title_content.gif'|urlres}"
                     alt="{"Управление блогом"|translate}" />

            </a><![endif]-->  
            <a href="{'/admin/blog'|url}"><i class="fa fa-comments fa-2x"></i>  {'Управление блогом'|translate}</a><br /><br />
        </li>
        {if ($isAdmin)}
        <li>
            <!--[if IE 7]><a href="{'/admin/config'|url}" title="{"Конфигурирование сайта"|translate}">
                <img src="{'/images/system/title_settings.gif'|urlres}"
                     alt="{"Конфигурирование сайта"|translate}" />
            </a><![endif]-->  
            <a href="{'/admin/config'|url}"><i class="fa fa-cog fa-2x"></i>  {'Конфигурирование сайта'|translate}</a><br /><br />
        </li>
        {/if}
        {if ($isAdmin)}
        <li>
            <!--[if IE 7]><a href="{'/admin/tools'|url}" title="{"Инструменты"|translate}">
                <img src="{'/images/system/title_tools.gif'|urlres}"
                     alt="{"Инструменты"|translate}" />
            </a><![endif]-->  
            <a href="{'/admin/tools'|url}"><i class="fa fa-wrench fa-2x"></i> {'Инструменты'|translate}</a><br /><br />
        </li>
        {/if}
    </ul>
</fieldset>
{* Модуль управление персоналом *}        
{in_array aNeedle='hr' aArray=$modules assign=isModule}
{if $isModule}
    <fieldset class="myfrm" style="font-style: italic;">
        <legend>{'Управление персоналом'|translate}</legend>
        {'Модуль предназначен для управления персоналом.<br />В него входят следующие разделы'|translate}:
    </fieldset>
{/if}
{include file='footer.tpl'}