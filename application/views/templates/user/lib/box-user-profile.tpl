<address>
<div class="box2 vcard">
    <h3>{"Профиль автора - '%s'"|translate:$user->username|escape}</h3>

    {if $user->profile->public_first_name|strlen > 0 ||
        $user->profile->public_last_name|strlen > 0}

    <div class="fn n">
            {if $user->profile->public_first_name|strlen > 0}
        <span class="given-name">
                    {$user->profile->public_first_name|escape}
        </span>
            {/if}
            {if $user->profile->public_last_name|strlen > 0}
        <span class="family-name">
                    {$user->profile->public_last_name|escape}
        </span>
            {/if}
    </div>
    {else}
    <div class="fn nickname">
            {'Публичные данные отсутствуют...'|translate}
    </div>
    {/if}

    {if $user->profile->public_email|strlen > 0}
    <div>
        Email:
        <a href="mailto:{$user->profile->public_email|escape}" class="email">
                {$user->profile->public_email|escape}
        </a>
    </div>
    {/if}

    {if $user->profile->public_home_phone|strlen > 0}
    <div class="tel">
        {'Тел.'|translate}
        (<span class="type">{'Домашний'|translate}</span>):
        <span class="value">
                {$user->profile->public_home_phone|escape}
        </span>
    </div>
    {/if}
    {if $user->profile->public_work_phone|strlen > 0}
    <div class="tel">
        {'Тел.'|translate}
        (<span class="type">{'Рабочий'|translate}</span>):
        <span class="value">
            {$user->profile->public_work_phone|escape}
        </span>
    </div>
    {/if}
    {if $user->profile->public_mobile_phone|strlen > 0}
    <div class="tel">
        {'Тел.'|translate}
        (<span class="type">{'Мобильный'|translate}</span>):
        <span class="value">
            {$user->profile->public_mobile_phone|escape}
        </span>
    </div>
    {/if}
</div>
</address>