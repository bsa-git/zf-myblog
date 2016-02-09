<div class="row">
    <div class="span4">
        <h3>{'Помощь'|translate}</h3>
        <ul>
            <li>
                <a href="https://github.com/bsa-git/zf-myblog" target="_blank" >GitHub</a>
            </li>
            <li>
                <a href="/index/license">{'Лицензия'|translate}</a>
            </li>
            <li>
                <a href="/index/readme">{'Краткая информация о продукте'|translate}</a>
            </li>
            <li>
                <a href="http://get.adobe.com/flashplayer" target="_blank" title="{'player_video_and_audio_files_is_not_installed'|translate}">{'Загрузить'|translate} Flash Player</a>
            </li>
        </ul>
    </div>
    <div class="span4">
        <h3>{'Выбрать схему'|translate}:</h3>
        <ul>
            <li {if $scheme == 'red-gray'}class='active'{/if}>
                <a href="{$smarty.server.REQUEST_URI|urlquery:'scheme':'red-gray'}" >{'Красно-Серая'|translate}</a>
            </li>
            <li {if $scheme == 'red-blue'}class='active'{/if}>
                <a href="{$smarty.server.REQUEST_URI|urlquery:'scheme':'red-blue'}">{'Красно-Синяя'|translate}</a>
            </li>
            <li {if $scheme == 'red-green'}class='active'{/if}>
                <a href="{$smarty.server.REQUEST_URI|urlquery:'scheme':'red-green'}">{'Красно-Зеленая'|translate}</a>
            </li>
        </ul>
    </div>
    <div class="span4">
        <h3>{'Выбрать язык'|translate}:</h3>
        <ul>
            <li {if $language == 'en'}class='active'{/if}>
                <a href="{$smarty.server.REQUEST_URI|urlquery:'locale':'en'}" >English</a>
            </li>
            <li {if $language == 'ru'}class='active'{/if}>
                <a href="{$smarty.server.REQUEST_URI|urlquery:'locale':'ru'}">Русский</a>
            </li>
            <li {if $language == 'uk'}class='active'{/if}>
                <a href="{$smarty.server.REQUEST_URI|urlquery:'locale':'uk'}">Український</a>
            </li>
        </ul>
    </div>
</div>