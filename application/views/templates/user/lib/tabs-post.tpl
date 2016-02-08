{* Панель с закладками  "tabs-post" *}
<div class="tabsHeader">
    <ul style="padding-left: 0px;">
        <li><a href="#1" {if $viewTab=="post"} class="tabActive"{/if} >{"Сообщение"|translate}</a></li>
        <li><a href="#2" {if $viewTab=="comments"} class="tabActive"{/if}>{"Комментарии"|translate}({$countComments})</a></li>
        {*
        <li><a href="#3">Tab3</a></li>
        <li><a href="#4">Tab4</a></li>
        *}
    </ul>
</div>
<div class="tabsContent">
    {* TAB-1 *}
    <div class="tabContent" style="display:{if $viewTab == 'post'}block{else}none{/if};">
        {$postContent}
    </div>
    {* TAB-2 *}
    <div class="tabContent" style="display:{if $viewTab == 'comments'}block{else}none{/if};">
        {$postComments}
    </div>
    {* TAB-3 *}
    {*
    <div class="tabContent" style="display:none;">
        <br /><div>3 Tab Content goes here</div>
    </div>
    *}
    {* TAB-4 *}
    {*
    <div class="tabContent" style="display:none;">
        <br /><div>4 Tab Content goes here</div>
    </div>
    *}
</div>
