<div id="left-menu" class="box2 menu-list">
    <h3>{'Модули'|translate}</h3>
    <ul style="list-style-type: none;">
        <li>
            {*<a href="{'/admin/index'|url}" title="{"Администрирование"|translate}">
                <img src="{'/images/system/admin.gif'|urlres}"
                     alt="{"Администрирование"|translate}" />
            </a>*} 
            <i class="icon-cog icon-white"></i> <a href="{'/admin/index'|url}">{'Администрирование'|translate}</a>
        </li>
        {in_array aNeedle='hr' aArray=$modules assign=isModule}
        {if $isModule}
            <li>
                <a href="{'/hr/index'|url}" title="{"Управление персоналом"|translate}">
                    <img src="{'/images/system/users.png'|urlres}"
                        alt="{"Управление персоналом"|translate}" />
                </a> 
                <a href="{'/hr/index'|url}">{'Управление персоналом'|translate}</a>
            </li>
        {/if}
    </ul>
</div>
