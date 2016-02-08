{include file='header.tpl' section='tools' 
    table=true 
    accordion=true 
    dialog=true 
    sections=true 
    ckeditor=true 
    windows=true
    tooltip=true
    leftcolumn='tools/lib/left-column.tpl'
}

{* Данные/Сообщения для передачи в JavaScript при работе с таблицей-гридом *}
{include file='lib/msg-box/table-grid.tpl'}

{* Загрузим скрипт BlogInfoManager.class.js *}
<script type="text/javascript" src="{'/js/BlogInfoManager.class.js'|urlres}"></script>

{* Загрузим скрипты описаний структур таблиц блогов *}
<script type="text/javascript" src="{'/js/table_models/admin.info.js'|urlres}"></script>

<fieldset id="edit-info-content" class="myfrm" style="display:none">
    <legend>{'Редактировать содержимое'|translate}</legend>
    <div id="ckeditor_content"></div>
    <br />
    <input type="submit" class="btn btn-primary" name="save_content" id="save_content" value="{'Сохранить изменения'|translate}" />
    <input type="submit" class="btn" name="cancel_content" id="cancel_content" value="{'Отмена'|translate}" />
</fieldset>

{* Аккордион*}
<ul class="accordion" id="accordion">
    <li class="section">
        <a href="0" class="title">
            <img src="{'/images/system/hint.gif'|urlres}"/> 
            {"Список информационной помощи"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-info" style="position:relative; width: 100%; height: 400px"></div><br />
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
    
    // Добавим параметры в список параметров для таблицы admin.info
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminInfo,
                    name: 'admin.blog_info',
                    container: 'table-admin-info',
                    url: '/admin/info',
                    rowsByPage: 10,
                    accordion: {id: 'accordion', section: 0}
                },
        search : { search: 'search-input' }
    });
        
    // Добавим параметры в список параметров для BlogInfoManager
    addScriptParams('BlogInfoManager', {
        container: 'info-win-container',
              url: '/admin/info',
        accordion: {id:'accordion-info-edit', section: 0, show: true}
    });

</script>
{/literal}

{include file='footer.tpl'}