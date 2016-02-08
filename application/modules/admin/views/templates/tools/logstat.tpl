{include file='header.tpl' 
    section='tools' 
    table=true 
    accordion=true  
    dialog=true 
    tooltip=true
    leftcolumn='tools/lib/left-column.tpl'
}

{*Вывод содержания*}
{* Данные/Сообщения для передачи в JavaScript при работе с таблицей-гридом *}
{include file='lib/msg-box/table-grid.tpl'}

{* Загрузим скрипт admin.users.js - описание структуры таблицы пользователей *}
<script type="text/javascript" src="{'/js/table_models/admin.log.js'|urlres}"></script>

{* Фильтр для значений таблицы данных *}
{include file='lib/filter-box1.tpl'}<br />

{* Аккордион *}
<ul class="accordion" id="accordion">
    <li class="section">
        <a href="0" class="title">
            <img src="{'/images/system/logStat20x20.png'|urlres}"/>
            {"Журнал статистики"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-log" style="position:relative; width: 100%; height: 400px"></div><br />
        </div>
    </li>
</ul>

{* Окно подсказки *}
<div id="floatTip"></div>

{literal}
    <script type="text/javascript">

        // Добавим параметры в список параметров для аккордиона
        addScriptParams('AccordionBox', {
            id: 'accordion',
            options : { mutuallyExclusive: false  },
            states: {disable: [], show: [0]}
        });

        // Добавим параметры в список параметров
        addScriptParams('MyTableGrid', {//BSA.tableModels.adminUsers
            table : {
                        ini_table: BSA.iniAdminLog,
                        name: 'admin.log_stat',
                        container: 'table-admin-log',
                        url: '/admin/log',
                        rowsByPage: 10,
                        accordion: {id: 'accordion', section: 0}
                    },
            filter : { form: 'filter-container_1'}
        });
    </script>
{/literal}
{include file='footer.tpl'}