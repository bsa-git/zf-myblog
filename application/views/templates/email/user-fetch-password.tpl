{$user->profile->first_name}, {'Ваш пароль входа на сайт'|translate}

{$user->profile->first_name}, {'Вы просили получить новый пароль, так как вы забыли ваш старый пароль.'|translate}

{'Ваш новый пароль смотрите ниже. Чтобы активировать этот пароль, нажмите на ссылку'|translate}

    {'Активировать пароль'|translate}: {$ActivateURL}?action=confirm&id={$user->getId()}&key={$user->profile->new_password_key}
    {'Имя входа пользователя'|translate}: {$user->username}
    {'Новый пароль'|translate}: {$user->_newPassword}

{'Если Вы не запрашивали создание нового пароля, пожалуйста, проигнорируйте это сообщение и Ваш пароль будет оставаться неизменным.'|translate}

{'С уважением'|translate},
{'Администратор WEB сайта'|translate}