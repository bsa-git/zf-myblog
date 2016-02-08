<div class="box2 menu-list">
    {if $authenticated}
        {if $isAdmin}
            <h3>{'Отладка'|translate}</h3>
            <ul id="box-debug" style="list-style-type: none;">
                <li>
                    <i class="icon-time icon-white"></i> <a href="{'admin/tools/profiler'|url}">{'Оценка быстродействия'|translate}</a>
                </li>
                <li>
                    <i class="icon-remove icon-white"></i> <a class="ajax-system-click" href="{'admin/tools/clearhist'|url}">{'Очистить историю profiler'|translate}</a>
                </li>
                <li>
                    <i class="icon-remove icon-white"></i> <a class="ajax-system-click" href="{'admin/tools/clearcache'|url}"> {'Очистить кеш'|translate}</a>
                </li>
            </ul>
        {/if}
    {/if}
</div>
