{foreach from=$treeComments key=key item=treeComment}
    {if $treeComment.parent}
        {if $treeComment.parent.is_autor}
            {assign var=viewUser value='Профиль автора'|translate}
        {else}
            {assign var=viewUser value='Профиль пользователя'|translate}
        {/if}
        <div class="itemComment" id="itemComment-{$treeComment.parent.id}">
            <div class="avatarComment">
                <a href="{$treeComment.parent.user_url|url}" title="{$viewUser}: {$treeComment.parent.username}">
                    <img src="{$treeComment.parent.user_img|urlres}" width="48" height="48" border="0" alt="{'Аватар пользователя'|translate}: {$treeComment.parent.username}"/></a>
            </div>
            <div class="panelComment">
                <a class="userComment" href="{$treeComment.parent.user_url|url}" title="{$viewUser}: {$treeComment.parent.username}">{$treeComment.parent.username}</a>
                <span class="dateComment" title="{'Дата, время комментария'|translate}">{$treeComment.parent.date}</span>
            </div>
            <div class="bodyComment">{$treeComment.parent.comment}</div>
            <div class="footerComment" style="clear: both">
                <a href="{$treeComment.parent.id}" class="replyComment {if $authenticated}authenticated{/if}" title="{'Ответить на комментарий'|translate}: {$treeComment.parent.username}">{'Ответить'|translate}</a>
                {if $isAdmin || $treeComment.parent.username == $identity->username}
                     | <a href="{$treeComment.parent.id}" class="editComment" title="{'Редактировать'|translate}">{'Редактировать'|translate}</a>
                     | <a href="{$treeComment.parent.id}" class="delComment" title="{'Удалить комментарий'|translate}">{'Удалить'|translate}</a>
                {/if}
            </div>
            
    {/if}
    {if $treeComment.sub}
        {include file='user/lib/comment-item.tpl'
            treeComments=$treeComment.sub
        }
    {/if}
    </div>
{/foreach}
