<div class="report-buttons">
    {if $view_visible}
        {capture assign='post_url'}{geturl username=$identity->username url=$post->url route='post'}{/capture}
        <a href="{$post_url|escape}?view=post" title="{'Просмотр сообщения'|translate}" ><i class="fa fa-search-plus fa-2x"></i><!--[if IE 7]><img id="content-user-view" class="report-button" src="{'/images/system/document_view21x21.png'|urlres}" /><![endif]--></a>
    {/if}
    {if $edit_visible}
        <a href="{'/blogmanager/preview'|url}?id={$post->getId()}" title="{'Редактировать сообщение в блоге'|translate}"><i class="fa fa-pencil-square-o fa-2x"></i><!--[if IE 7]><img id="content-blogmanager-edit" class="report-button" src="{'/images/system/edit21x21.png'|urlres}" /><![endif]--></a>
    {/if}
    {if $report_visible}
        <a href="{geturl route='post' username=$username url=$post->url}?report=true" target="_blank" title="{'Версия для печати'|translate}" ><i class="fa fa-print fa-2x"></i><!--[if IE 7]><img id="content-in-new-tab" class="report-button" src="{'/images/system/print16x16.png'|urlres}" /><![endif]--></a>
    {/if}
    {if $win_visible}
        <a href="#" id="content-in-new-window" title="{'Показать в отдельном окне'|translate}" ><i class="fa fa-list-alt fa-2x"></i><!--[if IE 7]><img class="report-button" src="{'/images/system/win2.gif'|urlres}" /><![endif]--></a>
    {/if}
    {if $infowin_id}
        <a href="#" id="{$infowin_id}" class="report-button help-info-win" title="{'Помощь'|translate}" ><i class="fa fa-question-circle fa-2x"></i><!--[if IE 7]><img class="report-button" src="{'/images/system/help24x24.png'|urlres}" /><![endif]--></a>
    {/if}
</div>