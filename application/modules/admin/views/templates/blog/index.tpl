{include file='header.tpl' 
    section='blog'
    leftcolumn='index/lib/left-column.tpl'
}
<dl>
    <dt class="dt-list">
        <i class="fa fa-comments fa-2x"></i> <!--[if IE 7]><img src="{'/images/system/title_content.gif'|urlres}" /><![endif]--> {'Управление блогом'|translate}
    </dt>
    <dd class="dd-list">
        <ul style="list-style-type: none;">
            <li>
                <a href="{'/blog/posts'|url}"><i class="fa fa-list fa-lg"></i> <!--[if IE 7]><img src="{'/images/system/comp_edit.gif'|urlres}" /><![endif]-->{'Список сообщений в блогах'|translate}</a>
            </li>
        </ul>
    </dd>
</dl>
{include file='footer.tpl'}