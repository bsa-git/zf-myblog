# zf-myblog 

Simple application implemented by the blog developed based on `Zend framework 1`.
The main idea of the application has been taken from the book 
`"Practical Web 2.0 Applications with PHP" (Authors: Quentin Zervaas)`.
The documentation on the Zend framework can be found on the website 
[Zend-Learn](http://framework.zend.com/learn/).
Examples of the application installation are for `OS "Windows"` and web server `Nginx`.

#### Main features of the application:

- Application implements a simple application management blog
- Expands with configuration file `application.ini` located in the `application/configs`
- Realized the localization of three languages: English, Russian and Ukrainian
- Ensures the registration process, user authentication and authorization
- The database created three users with the appropriate rights. The administrator (login = admin; pass = admin), editor (login = editor; pass = editor), user (login = user1; pass = user1)
- Uses a database type SqlLite `data/db/myblog.db`
- Full-text search is implemented by using the `Zend_Search_Lucene`, included in `Zend Framework 1`
- Used template [Smarty 2](http://www.smarty.net/) `vendor/smarty/smarty/libs`
- User message can consist of a text message itself and five for fitting resources (images, audio, video, streaming audio/video, geo-coordinates)
- You can give comments to the appropriate user message or other users comments
- The following types of resources. Images (jpeg, png, gif), audio (mp3), video (swf, flv, mov, mp4, m4v, f4v, YouTube), streaming video (RTMP, PSEUDOSTREAMIN, ADOBE HTTP STREAMING)
- You can create slide shows based on `mp3` audio format and the configuration file `json` format `public/upload/users/admin/files/video`
- For creating and editing a text message using editor [CKEditor](http://ckeditor.com/)
- To view and listen to video and audio resources used [FlowPlayer](http://flash.flowplayer.org/)
- To view video [YouTube](https://www.youtube.com/) uses the class [ProtoTube](http://scripts.downloadroute.com/ProtoTube-f4dbde0a.html)
- To display geo-coordinates using Google Maps ([JavaScript API V3](https://developers.google.com/maps/documentation/javascript/3.exp/reference))
- Geo-coordinates, you can place your notes, and more information on the area (photos, reports, etc.)
- User resource in the form of images, documents, video and audio files can be downloaded and viewed using web file manager [CKFinder](http://kcfinder.sunhater.com/)
- Added libraries such as [Zend-Framework 1](http://framework.zend.com/downloads/latest#ZF1) (this is basic application library), [mPDF](http://www.mpdf1.com/mpdf/index.php) (to create reports in PDF), [phpQuery](https://code.google.com/archive/p/phpquery/) (CSS DOM selector). These libraries are located in the `library`
- Also added plug-ins for working with arrays, strings, XML, HTTP, and others `application/plugins`
- Added site administration module `application/modules/admin`. With this module you can manage registered users, users messages, configure the application, and use a variety of tools
- With the site administration module, you can view and clear the error logs, reports and statistics. And you can generate reports in HTML and PDF formats
- On the client side using the librarys [Prototype](http://prototypejs.org/), [Scriptaculous](http://madrobby.github.io/scriptaculous/), [Bootstrap 2](http://twbs.github.io/bootstrap/2.3.2/), а также сервисы [Lightbox](http://lokeshdhakar.com/projects/lightbox2/), [Highlight](http://highlightjs.readthedocs.org/en/latest/#), [MyUi](http://pabloaravena.info/mytablegrid/index.html#), [Prototype-window](http://prototype-window.xilinus.com/index.html), [Prototype Accordion](https://github.com/deleteme/prototype-accordion), [Prototype Carousel](http://miedlar.com/dev/carousel), which are `public/js`

## Installing

### Prerequisites

- [PHP](http://php.net) version >= 5.4
- Apache2, Nginx web server or similar
- Composer

### Deploying

1. Clone [zf-myblog](https://github.com/bsa-git/silex-mvc) project with git.
2. Run `composer install`.
3. The application must install the latest version of the library `Zend-Framework 1` in the folder `library/Zend`. The latest version of the library can be downloaded - [here](http://framework.zend.com/downloads/latest#ZF1).
4. To generate reports in the format `PDF` can install the latest version of the library `mPDF` in the folder `library/mPDF`. The latest version of the library can be downloaded - [here](http://www.mpdf1.com/mpdf/index.php?page=Download).
5. Configure the Web server so that the entry point was `public/index.php`.
6. Set, if necessary, the appropriate permissions to write to `path/to/project/var`.
7. Access your project url with web browser.

## Configuration

### application.ini
In the configuration file created three sections: `production`, `testing`, `development`. Sections `testing` and `development` use common settings section `production`, but can also have their own specific settings.

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
