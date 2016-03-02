# zf-myblog

Простое приложение реализующее управление блогом, разработанное на базе `Zend framework 1`.
Основная идея приложения была взята из книги `"Создание приложений на PHP" (Автор: Квентин Зервас)`.
С документацией по `Zend framework 1` можно познакомиться на сайте [Zend-Learn](http://framework.zend.com/learn/). 
Примеры установки приложения приведены для `ОС "Windows"` и веб сервера `Nginx`.

#### Основные характеристики приложения:
- реализует простое приложение управления блогом;
- расширяется с помощью конфигурационного файла `application.ini`, расположенного в `application/configs`;
- реализована локализация для трех языков: английский, русский, украинский;
- обеспечивается процесс регистрации, аутентификации и авторизации пользователей;
- в БД созданы три пользователя с соответствующими правами. Администратор (login=admin; pass=admin) Редактор (login=editor; pass=editor) Пользователь (login=user1; pass=user1) ;
- использует БД типа SqlLite `data/db/myblog.db`;
- реализован полнотекстовый поиск с помощью конпонента `Zend_Search_Lucene`, входящего в `Zend_Framework 1`;
- используется шаблонизатор [Smarty 2](http://www.smarty.net/) `vendor/smarty/smarty/libs`;
- сообщение пользователя может состоять из самого текстового сообщения и пяти допольнительных ресурсов (изображения, аудио, видео, потоковое аудио/видео, гео-координаты);
- можно давать комментарии к сообщению пользователя или к соответствующим комментариям других пользователей;
- поддерживаются следующие типы ресурсов. Изображения (jpeg, png, gif), аудио (mp3), видео (swf, flv, mov, mp4, m4v, f4v, YouTube), потоковое видео (RTMP, PSEUDOSTREAMIN, ADOBE HTTP STREAMING);
- можно создавать слайд шоу на основе `mp3` аудио формата и файла конфигурации `json` формата `public/upload/users/admin/files/video`;
- для создания и редактирования текстового сообщения используется редактор [CKEditor](http://ckeditor.com/);
- для просмотра и прослушивания видео, аудио ресурсов используется [FlowPlayer](http://flash.flowplayer.org/);
- для просмотра видео с [YouTube](https://www.youtube.com/) используется класс [ProtoTube](http://scripts.downloadroute.com/ProtoTube-f4dbde0a.html);
- для отображения гео-координат используются Google карты ([JavaScript API V3](https://developers.google.com/maps/documentation/javascript/3.exp/reference));
- в гео-координатах можно размещать свои заметки и более подробную информацию о местности (фотографии, отчеты и т.д.);
- ресурсы пользователя в виде изображений, документов, видео и аудио файлов можно загружать и просматривать с помощью веб файлового менеджера [CKFinder](http://kcfinder.sunhater.com/);
- добавлены библиотеки такие как [Zend-Framework 1](http://framework.zend.com/downloads/latest#ZF1) (основная библиотека приложения), [mPDF](http://www.mpdf1.com/mpdf/index.php) (для создания отчетов в формате PDF), [phpQuery](https://code.google.com/archive/p/phpquery/) (CSS DOM селектор). Эти библиотеки находятся в папке `library`;
- также добавлены плагины для работы с массивами, строками, XML, HTTP и др. `application/plugins`;
- добавлен модуль администрирования сайта `application/modules/admin`. С помощью этого модуля можно управлять зарегистрированными пользователями, сообщениями пользователей, конфигурировать приложение, а также использовать различные инструменты;
- в модуле администрирования сайта можно просматривать и очищать логи ошибок, сообщений и статистики. А также можно создавать отчеты в HTML и PDF форматах;
- на стороне клиента используются библиотеки [Prototype](http://prototypejs.org/), [Scriptaculous](http://madrobby.github.io/scriptaculous/), [Bootstrap 2](http://twbs.github.io/bootstrap/2.3.2/), а также сервисы [Lightbox](http://lokeshdhakar.com/projects/lightbox2/), [Highlight](http://highlightjs.readthedocs.org/en/latest/#), [MyUi](http://pabloaravena.info/mytablegrid/index.html#), [Prototype-window](http://prototype-window.xilinus.com/index.html), [Prototype Accordion](https://github.com/deleteme/prototype-accordion), [Prototype Carousel](http://miedlar.com/dev/carousel), которые находятся в `public/js`.



## Инсталяция

### Предварительные требования

- [PHP](http://php.net) version >= 5.4
- веб сервер Apache2, Nginx или похожие
- Composer

### Развертывание

1. Клонировать [zf-myblog](https://github.com/alvk4r/silex-enhanced) проект с помощью git.
2. Выполнить `composer install`.
3. Для работы приложения необходимо установить последнюю версию библиотеки `Zend-Framework 1` в папку `library/Zend`. Последняя версия библиотеки может быть загружена - [здесь](http://framework.zend.com/downloads/latest#ZF1). 
4. Для формирования отчетов в формате `PDF` можно установить последнюю версию библиотеки  `mPDF` в папку `library/mPDF`. Последняя версия библиотеки может быть загружена - [здесь](http://www.mpdf1.com/mpdf/index.php?page=Download). 
5. Сконфигурируйте веб сервер, чтобы точка входа была `public/index.php`.
6. Установите, если необходимо, соответсвующие права на запись в `path/to/project/var`.
7. Введите адрес сайта в броузер (пр. http://zf-myblog.ru/)

## Конфигурация

### application.ini
В файле конфигурации созданы три раздела: `production`, `testing`, `development`. Разделы `testing` и `development` используют общие настройки раздела `production`, а также могут иметь свои специфические настройки.

```ini
 [production]
    ; PHP settings
    phpSettings.display_startup_errors = 0
    phpSettings.display_errors = 0

    ;Indicate the path and classname of the bootstrap
    bootstrap.path = APPLICATION_PATH "/Bootstrap.php"
    bootstrap.class = "Bootstrap"
    appnamespace = "Application"
    ...
[testing : production]
    phpSettings.display_startup_errors = 1
    phpSettings.display_errors = 1

[development : production]
    phpSettings.display_startup_errors = 1
    phpSettings.display_errors = 1
    resources.frontController.params.displayExceptions = 1
```
