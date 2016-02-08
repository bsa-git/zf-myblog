{include file='header.tpl' section='blog' table=true accordion=true dialog=true utilities=true tooltip=true}

{* Данные/Сообщения для передачи в JavaScript при работе с таблицей-гридом *}
{include file='lib/msg-box/table-grid.tpl'}

{* Загрузим скрипты описаний структур таблиц блогов *}
<script type="text/javascript" src="{'/js/table_models/admin.blog_posts.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/table_models/admin.blog_posts_tags.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/table_models/admin.blog_posts_images.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/table_models/admin.blog_posts_audio.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/table_models/admin.blog_posts_video.js'|urlres}"></script>
<script type="text/javascript" src="{'/js/table_models/admin.blog_posts_locations.js'|urlres}"></script>


{* Фильтр для значений таблицы данных *}
{include file='lib/filter-box1.tpl'}<br />

{* Аккордион *}
<ul class="accordion" id="accordion">
    <li class="section">
        <a href="0" class="title">
            <img src="{'/images/system/comp_edit.gif'|urlres}"/> 
            {"Сообщения"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-posts" style="position:relative; width: 100%; height: 400px"></div><br />
        </div>
    </li>
    <li class="section">
        <a href="1" class="title">
            <img src="{'/images/system/link.gif'|urlres}"/> 
            {"Метки"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-posts-tags" style="position:relative; width: 100%; height: 400px"></div><br />
        </div>
    </li>
    <li class="section">
        <a href="2" class="title">
            <img src="{'/images/system/views16x16.png'|urlres}"/> 
            {"Изображения"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-posts-images" style="position:relative; width: 100%; height: 400px"></div><br />
        </div>
    </li>
    <li class="section">
        <a href="3" class="title">
            <img src="{'/images/system/music18x18.png'|urlres}"/> 
            {"Аудио"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-posts-audio" style="position:relative; width: 100%; height: 400px"></div><br />
        </div>
    </li>
    <li class="section">
        <a href="4" class="title">
            <img src="{'/images/system/film18x18.png'|urlres}"/> 
            {"Video"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-posts-video" style="position:relative; width: 100%; height: 400px"></div><br />
        </div>
    </li>
    <li class="section">
        <a href="5" class="title">
            <img src="{'/images/system/Weblink.png'|urlres}"/> 
            {"Географические координаты"|translate}
        </a>
        <div class="toggle">
            <div id="table-admin-posts-locations" style="position:relative; width: 100%; height: 400px"></div><br />
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
    
    
    // Добавим параметры в список параметров для таблицы admin.blog_posts
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminPosts,
                    name: 'admin.blog_posts',
                    container: 'table-admin-posts',
                    url: '/admin/blog',
                    rowsByPage: 10,
                    master: 1,
                    accordion: {id: 'accordion', section: 0}
                },
        search : { search: 'search-input' },
        filter : { form: 'filter-container_1'}
    });
    // Добавим параметры в список параметров для таблицы admin.blog_posts_tags
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminPostTags,
                    name: 'admin.blog_posts_tags',
                    container: 'table-admin-posts-tags',
                    url: '/admin/blog',
                    rowsByPage: 10,
                    slave: 1,
                    accordion: {id: 'accordion', section: 1}
                }
    });
    // Добавим параметры в список параметров для таблицы admin.blog_posts_images
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminPostImages,
                    name: 'admin.blog_posts_images',
                    container: 'table-admin-posts-images',
                    url: '/admin/blog',
                    rowsByPage: 10,
                    slave: 1,
                    accordion: {id: 'accordion', section: 2}
                }
    });
    // Добавим параметры в список параметров для таблицы admin.blog_posts_audio
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminPostAudio,
                    name: 'admin.blog_posts_audio',
                    container: 'table-admin-posts-audio',
                    url: '/admin/blog',
                    rowsByPage: 10,
                    slave: 1,
                    accordion: {id: 'accordion', section: 3}
                }
    });
    // Добавим параметры в список параметров для таблицы admin.blog_posts_video
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminPostVideo,
                    name: 'admin.blog_posts_video',
                    container: 'table-admin-posts-video',
                    url: '/admin/blog',
                    rowsByPage: 10,
                    slave: 1,
                    accordion: {id: 'accordion', section: 4}
                }
    });
    // Добавим параметры в список параметров для таблицы admin.blog_posts_locations
    addScriptParams('MyTableGrid', {
        table : {
                    ini_table: BSA.iniAdminPostLocations,
                    name: 'admin.blog_posts_locations',
                    container: 'table-admin-posts-locations',
                    url: '/admin/blog',
                    rowsByPage: 10,
                    slave: 1,
                    accordion: {id: 'accordion', section: 5}
                }
    });
</script>
{/literal}

{include file='footer.tpl'
    leftcolumn='blog/lib/left-column.tpl'
}