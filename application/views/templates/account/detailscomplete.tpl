{include file='header.tpl' section='account'}

<p class="lead">
    {'Спасибо вам'|translate} {$user->profile->first_name|escape},
    {'ваши пользовательские данные были успешно обновлены'|translate}.
</p>

{include file='footer.tpl'}