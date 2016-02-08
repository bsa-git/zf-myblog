    </div>
</div>

<div id="left-container" class="column">
    {*Поиск данных*}
    {include file='lib/box-search.tpl'}
    {*Левая колонка*}
    {if isset($leftcolumn) && $leftcolumn|strlen > 0}
        {include file=$leftcolumn}
    {/if}
</div>

<div id="right-container" class="column">
    {*Вывод сообщений*}
    {include file='lib/box-messages.tpl'}
    {*Выход из режима администрирования*}
    {include file='lib/box-out.tpl'}
    {*Правая колонка*}
    {if isset($rightcolumn) && $rightcolumn|strlen > 0}
        {include file=$rightcolumn}
    {/if}
    {*Предупреждение при отключеном JavaScript*}
    <noscript>
        <div class="box">
            {"Если хотите получить дополнительные возможности, то включите JavaScript!"|translate}
            <a href="http://www.google.ru/support/bin/answer.py?answer=23852">{"Как"|translate}?</a>
        </div>
    </noscript>
</div>

<div id="footer">
    Practical PHP Web 2.0 Applications, by Quentin Zervaas.<br />
    Zend Framework v.{$zend_version}
</div>
</body>
</html>