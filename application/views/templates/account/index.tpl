{include file='header.tpl' section='account' isHorizontalLine=true}

<p class="lead">{'Мы Вас приветствуем'|translate} {$identity->first_name}.</p>

<ul style="list-style: none;">
    {if !$isCommentator}
        <li><a href="{'/blogmanager/edit'|url}"><i class="fa fa-comment-o fa-2x"></i> {'Создать новое сообщение в блоге'|translate}</a><br /><br /></li>
        <li><a href="{'/blogmanager'|url}"><i class="fa fa-comments fa-2x"></i> {'Посмотреть все сообщения в блоге'|translate}</a><br /><br /></li>
    {/if}
    <li><a href="{'/account/details'|url}"><i class="fa fa-user fa-2x"></i> {'Редактировать профиль'|translate}</a><br /><br /></li>
</ul>

{include file='footer.tpl'}