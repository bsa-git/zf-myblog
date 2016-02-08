{* Контейнера для окна инф. помощи *}
<div id="info-win-container" style="">
    {* Аккордион *}
    <ul class="accordion" id="accordion-info-edit">
        {foreach name=locales item=local from=$list_locales}
            <li class="section">
                <a href="{$smarty.foreach.locales.index}" class="title">
                    <img src="{"/images/system/flags/`$local`_1.png"|urlres}"/> 
                    {$local|upper}
                </a>
                <div class="toggle">
                    <div class="panel-row-h800">
                        <div class="info-edit" style="display: block">
                            <a href="#" local="{$local}" class="info-edit-title" title="{'Редактировать название'|translate}">
                                <i class="fa fa-pencil fa-2x"></i><!--[if IE 7]><img src="{'/images/system/draw.png'|urlres}"><![endif]-->
                            </a>
                        </div>
                        <div class="section-title" id="section-title-{$local}">#{ldelim}title_{$local}{rdelim}</div>
                    </div>
                    <div class="panel-row-gray">
                        <div class="info-edit" style="display: block">
                            <a href="#" local="{$local}" class="info-edit-content" title="{'Редактировать содержимое'|translate}">
                                <i class="fa fa-pencil-square-o fa-2x"></i><!--[if IE 7]><img src="{'/images/system/comp_edit.gif'|urlres}"><![endif]-->
                            </a>
                            <a href="{$smarty.foreach.locales.index}" class="info-edit-close" title="{'Закрыть'|translate}">
                                <i class="fa fa-times fa-2x"></i><!--[if IE 7]><img src="{'/images/system/delete_button.gif'|urlres}"><![endif]-->
                            </a>
                        </div>
                        <div class="section-content" id="section-content-{$local}">#{ldelim}content_{$local}{rdelim}</div>
                        <br />
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
</div>
