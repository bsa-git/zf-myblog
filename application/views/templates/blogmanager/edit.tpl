{include file='header.tpl' 
    section='blogmanager' ckeditor=true
    leftcolumn='blogmanager/lib/left-column.tpl'
    rightcolumn='blogmanager/lib/right-column.tpl'
}

{*Выведем форму записи в блоге*}
{$formBlogPost}

{* Создадим обьект редактора Html *}
{literal}
<script type="text/javascript">
  addScriptParams('CKEditorHtml', {container: 'ckeditor_description', config:{toolbar : 'min2'}});
  addScriptParams('CKEditorHtml', {container: 'ckeditor_content', config:{toolbar : 'medium'}});
</script>
{/literal}
{* Скрипт обработки событий суммарных данных (месячных, меток) *}
<script type="text/javascript" src="{'/js/BlogSummary.class.js'|urlres}"></script>
{literal}
    <script type="text/javascript">
        addScriptParams('BlogSummary', {container: 'blog-posts-preview'});
    </script>
{/literal}

{*Окончание страницы*}
{include file='footer.tpl'}
