{* Общие данные для передачи в JavaScript *}
<div class="msg-box" style="display: none">
    {* Определение броузера IE версии 5.0 и выше... *}
    <!--[if gte IE 5]><p id="is_ie"></p><![endif]>-->
    {* Базовый путь к ресурсам *}
    {*<p id="urlBase">{'/'|urlres}/index.php</p>*}
    <p id="urlBase">{'/'|urlres}</p>
    <p id="urlRes">{'/'|urlres}</p>
    <p id="urlMVC">{$url_mvc|escape}</p>
    <p id="titleSite">{$title}</p>
    <p id="languageSite">{getlang}</p>
    {* User logo name *}
    <p id="userMainName">{$user_main_name}</p>
    {* User logo url *}
    <p id="userLogoUrl">{$logo_url}</p>
    <p id="msgError">{'error'|translate}</p>
    <p id="msgWarning">{'warning'|translate}</p>
    <p id="msgInformation">{'information'|translate}</p>
    <p id="msgCaution">{'caution'|translate}</p>
    <p id="msgMessage">{'message'|translate}</p>
    <p id="msgImage">{'Изображение'|Translate}</p>
    <p id="msgOf">{'из'|Translate}</p>
    <p id="msgDisplayInNewWindow">{'Показать в отдельном окне'|Translate}</p>
    <p id="msgTitleInformation">{'Информация'|translate}</p>
    <p id="msgDetails">{'Подробнее...'|translate}</p>
    <p id="msgCancel">{'Отмена'|Translate}</p>
    <p id="msgPlay">{'Играть'|Translate}</p>
    <p id="msgStop">{'Стоп'|Translate}</p>
    
    <p id="msgSaving">{'Сохраняем...'|Translate}</p>
    <p id="msgClickToEdit">{'Кликните мышью, чтобы редактировать'|Translate}</p>
    <p id="msgLoadingMessages">{'Загружаются сообщения...'|Translate}</p>
    <p id="msgIsPreparingReport">{'Идет подготовка отчета...'|Translate}</p>
    <p id="msgAlreadyPassed">{'Прошло'|Translate}</p>
    <p id="msgMinutes">{'мин.'|Translate}</p>
    <p id="msgSeconds">{'сек.'|Translate}</p>
    <p id="msgTurnOffBlockerWindows">{'Отключите блокировщик окон'|Translate}</p>
    <p id="msgInput">{'Ввод'|Translate}</p>
    <p id="msgErrNoAccessToInformationResources">{"Нет доступа к соответствующему информационному ресурсу - '#[video_res]'"|Translate}</p>
    <p id="msgOrHere">{'или здесь'|Translate}</p>
    <p id="msgOr">{'или'|Translate}</p>
    <p id="msgHere">{'здесь'|Translate}</p>
    <p id="msgСlean">{'Очистить'|Translate}</p>
    <p id="msgSiteSectionIsDevelopment">{'Раздел сайта находится в разработке'|Translate}</p>
    <p id="msgNotOpenReportWindow">{'Не открылось окно вывода отчета? Возможно включен режим запрета всплывающих окон.'|Translate}</p>
    
    <p id="msgErrVideo">{'Ошибка видео'|Translate}</p>
    <p id="msgErrCode">{'Код ошибки'|Translate}</p>
    <p id="msgErrMessage">{'Сообщение об ошибке'|Translate}</p>
    
</div>