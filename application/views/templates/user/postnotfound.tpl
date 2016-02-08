{include file='header.tpl' leftcolumn='user/lib/left-column.tpl'}

<p>
    <a href="{geturl username=$user->username route='user'}">
        {'Вернуться к блогу автора'|translate} - {$user->username|escape}
    </a>
</p>

{include file='footer.tpl'}
