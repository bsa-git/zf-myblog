{include file='header.tpl' section='register'}

{*Выведем сообщение об успешной регистрации пользователя*}
<p class="lead">
    {'Спасибо вам'|translate} 
    {$user->profile->first_name|escape}.
    {'Поздравляем вас с успешной регистрацией'|translate}!<br /> 
    {'Ваш пароль был передан на Emaile'|translate} ({$user->profile->email|escape}).
</p>
{include file='footer.tpl'}