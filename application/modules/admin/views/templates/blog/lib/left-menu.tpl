<div class="box3 menu-list">
    <h3>{'Управление блогом'|translate}</h3>
    <ul style="list-style-type: none;">
        {if ($isAdmin)||($isEditor)}
        <li>
            <a href="{'/blog/posts'|url}" title="{"Список сообщений в блогах"|translate}">
                <img src="{'/images/system/comp_edit.gif'|urlres}"
                     alt="{"Список сообщений в блогах"|translate}" />
            </a>
            <a href="{'/blog/posts'|url}">{'Список сообщений в блогах'|translate}</a>
        </li>
        {/if}
    </ul>
    
</div>
