/**
 * BlogLocations - Class
 *
 * С помощью класса вы можете:
 *  - отобразить гео карту
 *  - отображать маркеры на карте
 *  - переходить по геоссылкам на соответствующий маркер и видеть его содержимое
 *
 * JavaScript
 *
 * Copyright (c) 2011 Бескоровайный Сергей
 *
 * @author     Бескоровайный Сергей <bs261257@gmail.com>
 * @copyright  2011 Бескоровайный Сергей
 * @license    BSD
 * @version    1.00.00
 * @link       http://my-site.com/web
 */

//google.load('maps', '2');

BSA.BlogLocations = Class.create({
    url: null,
    post_id: null, // ID для сообщения
    user_id: null, // ID для пользователя, создателя сообщения
    location_id: null,
    container: null, // DOM element in which map is shown
    map: null, // The instance of Google Maps

    markers: new Hash(), // holds all markers added to map

    markerTemplate: null,
    accordion: null, // Аккордион
    isDownloaded: false, // Признак загрузки изображений
    actual: false, // Признак состояния аккордиона (открыт или закрыт)

    initialize: function (params)//container, user_id, post_id
    {

        this.markerTemplate = new Template(''
                + '<div id="container_marker_#{location_id}">'
                + '<table style="clear:both" width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
                + '<tr>'
                + '<th> <div id="description_#{location_id}" valign="top" >#{desc}</div></th>'
                + '</tr>'
                + '<tr>'
                + '<td id="content_#{location_id}" align="left" valign="top" >#{content}</td>'
                + '</tr>'
                + '<tr>'
                + '<td align="left" valign="top" style="padding:5px;">#{details}</td>'
                + '</tr>'
                + '<tr>'
                + '<td align="center" valign="top" height="32px">'
                + ' <a id="close_#{location_id}" href="#" title="' + lb.getMsg('msgCloseWinMarker') + '"><i class="fa fa-times fa-2x"></i><!--[if IE 7]><img src="' + lb.getMsg('urlRes') + '/images/system/delete.png" ><![endif]--></a>'
                + ' <a id="views_#{location_id}" href="#" title="' + lb.getMsg('msgDisplayInNewWindow') + '"><i class="fa fa-list-alt fa-2x"></i><!--[if IE 7]><img  src="' + lb.getMsg('urlRes') + '/images/system/win2.gif" ><![endif]--></a>'
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>'
                );


        this.url = lb.getMsg('urlBase') + '/blogmanager/getlocations';
        this.post_id = params.post_id;
        this.user_id = params.user_id;
        this.container = $(params.container);

        if (!this.container)
            return;

        this.mapContainer = this.container.down('.map');

        if (!this.mapContainer)
            return;

        // Назначим события для ссылок на координаты
        this.container.select('.geo').each(function (s) {
            Event.observe(s, 'click', this.onGoToLocation.bindAsEventListener(this));
        }.bind(this));

        // Определим наличие аккордиона
        if (params.accordion) {
            this.accordion = params.accordion;
            // Подпишемся на события в аккордионе
            this._subscribeAccordionEvents();
        } else {
            // Загрузим карту
            this.loadMap();
        }


    },
    //--------------- Загрузка карты --------------

    loadMap: function ()
    {
        // Create map
        this.map = new google.maps.Map(this.mapContainer, {
            center: new google.maps.LatLng(0, 0),
            zoom: 1,
            mapTypeId: google.maps.MapTypeId.HYBRID,// HYBRID, SATELLITE
            // Add map type control
            mapTypeControl: true,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
                position: google.maps.ControlPosition.TOP_LEFT
            },
            // Add scale
            scaleControl: true,
            scaleControlOptions: {
                position: google.maps.ControlPosition.BOTTOM_RIGHT
            },
            // Add fullscreen
            fullscreenControl: true,
            // Add rotate
            rotateControl: true
        });

        // Create InfoWindow
        this.infoWindow = new google.maps.InfoWindow({
        });

        // Load location info
        var options = {
            parameters: {
                post_id: this.post_id,
                user_id: this.user_id
            },
            onSuccess: this.loadLocationsSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
        BSA.Sys.message_write(lb.getMsg('msgLoadCoordinateMarkers'));
    },
    
    loadLocationsSuccess: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.locations == null) {
            BSA.Sys.message_write(lb.getMsg('msgErrorLoadCoordinateMarkers'));
            return;
        }

        // Create markers
        json.locations.each(function (location) {
            this.addMarkerToMap(
                    location.location_id,
                    location.latitude,
                    location.longitude,
                    location.description,
                    location.content,
                    location.details
                    );
        }.bind(this));
        
        // Zoom and center map
        this.zoomAndCenterMap();
        BSA.Sys.message_clear();
    },

    //---------------- Управление маркерами -----------------

    addMarkerToMap: function (id, lat, lng, desc, cont, details)
    {
        var self = this;
        //-------------------------
        this.removeMarkerFromMap(id);

        var mapsMarker = new google.maps.Marker({
            map: this.map,
            position: new google.maps.LatLng(lat, lng),
            title: desc}
        );

        this.markers.set(id, mapsMarker);
        this.markers.get(id).location_id = id;
        this.markers.get(id).lat = lat;
        this.markers.get(id).lng = lng;
        this.markers.get(id).desc = desc;
        this.markers.get(id).content = cont;
        this.markers.get(id).details = details;
        
        // Add marker click listener 
        google.maps.event.addListener(this.markers.get(id), 'click', function () {
            self.clickMarker(this);
        });


        // Add link to details
        if (details) {
            details = '<a href="' + details + '">' + lb.getMsg('msgDetails') + '</a>';
        }

        // Bild template
        var html = this.markerTemplate.evaluate({
            'location_id': id,
            'lat': lat,
            'lng': lng,
            'desc': desc,
            'content': cont,
            'details': details
        });

        var node = Builder.build(html);
        this.markers.get(id).node = node;

        // Найдем обьекты
        var location_close = node.getElementsBySelector('a#close_' + id)[0];
        var location_views = node.getElementsBySelector('a#views_' + id)[0];

        // Установим атрибуты обьектов
        location_close.setAttribute('location_id', id);
        location_views.setAttribute('location_id', id);

        // Установим события обьектов
        location_close.observe('click', this.onCloseWinMarker.bindAsEventListener(this));
        location_views.observe('click', this.onOpenViewsWin.bindAsEventListener(this));

        return this.markers.get(id);
    },
    removeMarkerFromMap: function (location_id)
    {
        if (!this.hasMarker(location_id))
            return;
        
        // Remove marker from map
        this.markers.get(location_id).setMap(null);
        this.markers.unset(location_id);
        
        // Zoom and center map
        this.zoomAndCenterMap();

    },
    hasMarker: function (location_id)
    {
        var location_ids = this.markers.keys();
        var index = location_ids.indexOf(String(location_id));

        return index >= 0;
    },
    clickMarker: function (marker)
    {
        // Сохраним значение кода координаты
        this.location_id = marker.location_id;
        
        // Open info windows
        this.infoWindow.setContent(marker.node);
        this.infoWindow.open(marker.getMap(), marker);

        // Выполним действия по модификации, после того как появяться элементы в DOM
        this.idTimeout = window.setTimeout(function () {
            // Изменим содержимое окна
            this.modifyMarkerImages();

            // Изменить события ссылок содержания маркера
            this.modifyMarkerLinks();

            window.clearTimeout(this.idTimeout);
        }.bind(this), 500);
    },
    //Изменить содержание маркера
    //
    // - пр. преобразовать тег <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //   в теги -> <a href="/upload/users/user1/images/bsa.jpg"" rel="lightbox[location]">
    //               <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //             </a>
    modifyMarkerImages: function ()
    {
        // Обьект маркера
        var content = $('content_' + this.location_id);
        var scr = '';//'[title="yummy!"]'
        var rel_lightbox = content.select('[rel="lightbox[location]"]');
        if (rel_lightbox.size() > 0) {
            return;
        }

        content.select('img').each(function (img) {
            scr = img.readAttribute('src');
            if (scr.include('/.thumbs/')) {
                scr = scr.sub('.thumbs/', '');
                img.wrap('a', {
                    'href': scr,
                    'rel': 'lightbox[location]'
                });
            }
        });
    },
    //Изменить события ссылок содержания маркера
    // При нажатии на ссылку, должно открываться отдельное окно
    modifyMarkerLinks: function ()
    {
        // Обьект маркера
        var isModifiedEventsMarker = this.markers.get(this.location_id).isModifiedEventsMarker;
        if (isModifiedEventsMarker) {
            return;
        } else {
            this.markers.get(this.location_id).isModifiedEventsMarker = true;
        }

        var arrListFileResources = $A(BSA.Sys.settings.list_file_resources);
        var isFileResource = false;
        var href = '';

        // Содержание кода HTML
        var content = $('container_marker_' + this.location_id);

        // Назначим для ссылок, кроме ссылок на фотографии
        // новые события, которые будут открывать отдельное окно для этой ссылки
        content.select('a').each(function (a) {
            rel = a.readAttribute('rel');
            if (!rel) {
                // Получим URL ссылки
                href = a.readAttribute('href');

                // Если ссылка не есть файловый ресурс, то назначим
                // обработчик
                arrListFileResources.each(function (s) {
                    if (href.endsWith(s)) {
                        isFileResource = true;
                        return;
                    }
                });

                if (!isFileResource && href !== "#") {
                    a.observe('click', this.onOpenWinForURL.bindAsEventListener(this));
                } else {
                    isFileResource = false;
                    a.writeAttribute('target', '_blank');
                }
            }
        }.bind(this));
    },
    //-------------- Работа с окнами ---------------

    onCloseWinMarker: function (e)
    {
        Event.stop(e);
        this.infoWindow.close();
    },
    onOpenViewsWin: function (e)
    {

        Event.stop(e);

        var marker = this.markers.get(this.location_id);
        // Получить размер содержания координаты
        var content = $('content_' + this.location_id);
        var dimentions = content.getDimensions();

        var win = new Window({
            className: "mac_os_x",
            //className: "dialog",
            title: lb.getMsg('msgTitleInformation'),
            width: (dimentions.width + 10 > 300) ? dimentions.width + 10 : 300,
            height: (dimentions.height + 50 > 200) ? dimentions.height + 50 : 200,
            destroyOnClose: true,
            recenterAuto: false
        });
        var win_content = '<div class="win-content" id="win_content_' + this.location_id + '" >'
                + '<table width="100%" height="100%" border="0" cellpadding="5" cellspacing="0">'
                + '<tr>'
                + '<th>'
                + marker.desc
                + '</th>'
                + '</tr>'
                + '<tr>'
                + '<td align="left" valign="top" >'
                //+ marker.content
                + content.innerHTML
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>';
        win.getContent().update(win_content);
        win.showCenter();

        this.infoWindow.close();
    },
    onOpenWinForURL: function (event)
    {
        // Отменим поведение по умолчанию
        Event.stop(event);

        // Получим элемент, вызвавший событие
        var element = Event.element(event);

        // Получим URL ссылки
        var href = element.readAttribute('href');

        // Откроем окно для этого URL
        //this.onOpenWinWithURL(href);
        var win = new Window({
            className: "mac_os_x",
            title: lb.getMsg('msgTitleInformation'),
            top: 0,
            left: 0,
            width: 600,
            height: 400,
            url: href,
            showEffectOptions: {
                duration: 1.5
            }
        })
        win.showCenter();
        win.show();
    },
    //-------------- Дополнительные ф-ии ---------------

    zoomAndCenterMap: function ()
    {
        var zoom = 0;
        var bounds = new google.maps.LatLngBounds();
        var keys = this.markers.keys().sort();
        //--------------------------------------
        keys.each(function (key) {
            bounds.extend(this.markers.get(key).getPosition());
        }.bind(this));

        if (!bounds.isEmpty()) {
            this.map.fitBounds(bounds);
        }

        if (bounds.isEmpty()) {
            this.map.setCenter(new google.maps.LatLng(0, 0));
        } else if (keys.size() == 1) {
            this.map.setCenter(bounds.getCenter());
            zoom = Math.max(1, this.map.getZoom() - 6);
            this.map.setZoom(zoom);
        } else {
            this.map.setCenter(bounds.getCenter());
            zoom = Math.max(1, this.map.getZoom());
            this.map.setZoom(zoom);
        }
    },
    onGoToLocation: function (event)
    {
        // Отменим поведение по умолчанию
        Event.stop(event);

        // Получим элемент, вызвавший событие
        var element = Event.element(event);

        // Получим соответствующий маркер
        var location_id = element.readAttribute('location_id');
        var marker = this.markers.get(location_id);

        // Откроем инф. по маркеру
        this.clickMarker(marker);

    },
    //========== РАБОТА С АККОРДИОНОМ ===========//

    // Подпишемся на события в аккордионе
    _subscribeAccordionEvents: function () {
        var self = this;
        var indexSection = self.accordion.section;
        var section;
        var idTimeout;
        //-----------------------------
        var boxAccordions = scriptInstances.get('AccordionBox');
        boxAccordions.each(function (box) {
            if (box.id == self.accordion.id) {

                box.onHiddenSectionEvent.push({
                    client: self,
                    handlerEvent: self.onHiddenSectionEvent
                });

                box.onShownSectionEvent.push({
                    client: self,
                    handlerEvent: self.onShownSectionEvent
                });

                // Получим соответсвующую секцию и откроем ее
                if (self.accordion.show) {
                    idTimeout = window.setTimeout(function () {
                        section = box.accordion.sections[indexSection];
                        box.accordion.showSection(section);
                        window.clearTimeout(idTimeout);
                    }, 300);
                }
            }
        })
    },
    // Свернуть секцию в аккордионе
    onHiddenSectionEvent: function (self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if (hrefSection == self.accordion.section) {
            self.actual = false;
        }
    },
    // Развернуть секцию в аккордионе
    onShownSectionEvent: function (self, params) {
        var section = params.section.elements.section;
        var hrefSection = section.down('a').readAttribute('href');
        if (hrefSection == self.accordion.section) {
            self.actual = true;

            // Загрузим изображения на страницу, если они еще не были загружены
            if (!self.isDownloaded) {
                self.isDownloaded = true;

                // Загрузим карту
                self.loadMap();
            }
        }
    }
});

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(BlogLocations: [new BlogLocations(param1), ... ,new BlogLocations(paramN)])
BSA.BlogLocations.RegRunOnLoad = function () {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogLocations');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var locations = scriptInstances.get('BlogLocations');
        if (locations) {
            locations.push(new BSA.BlogLocations(param));
        } else {
            scriptInstances.set('BlogLocations', [new BSA.BlogLocations(param)]);
        }
    };
    // Создание обьектов
    if (params) {
        params.each(function (param) {
            createObject(param);
        });
    } else {
        createObject();
    }
}
runOnLoad(BSA.BlogLocations.RegRunOnLoad);