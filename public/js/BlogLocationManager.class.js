/**
 * BlogLocationManager - Class
 *
 * С помощью класса вы можете:
 *  - отобразить гео карту
 *  - создавать и управлять маркерами на карте
 *  - редактировать титл, содержимое и подробное содержимое маркера
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
BSA.BlogLocationManager = Class.create({
    url: null,
    post_id: null, // ID of the blog post being managed
    location_id: null,
    container: null, // DOM element in which map is shown
    map: null, // The instance of Google Maps
    geocoder: null, // Used to look up addresses

    //markers   : $H({}), // holds all markers added to map
    markers: new Hash(), // holds all markers added to map

    markerTemplate: null,
    ckeditor: null,
    inPlaceEditors: new Hash(), // Редакторы описания координаты

    idTimeout: null,
    initialize: function (params) {

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
                + '<tr>'//times plus
                + '<td align="center" valign="top" height="32px">'
                + ' <a id="delete_#{location_id}" href="#" title="' + lb.getMsg('msgDeleteCoordinate') + '"><i class="fa fa-scissors fa-2x"></i><!--[if IE 7]><img src="' + lb.getMsg('urlRes') + '/images/system/delete.png" ><![endif]--></a>'
                + ' <a id="edit_#{location_id}" href="#" title="' + lb.getMsg('msgEditContent') + '"><i class="fa fa-pencil fa-2x"></i><!--[if IE 7]><img  src="' + lb.getMsg('urlRes') + '/images/system/edit.png" ><![endif]--></a>'
                + ' <a id="details_#{location_id}" href="#" title="' + lb.getMsg('msgEditDetails') + '"><i class="fa fa-pencil-square-o fa-2x"></i><!--[if IE 7]><img src="' + lb.getMsg('urlRes') + '/images/system/document.gif" ><![endif]--></a>'
                + ' <a id="copy_#{location_id}" href="#" title="' + lb.getMsg('msgCopyCoordinate') + '"><i class="fa fa-plus fa-2x"></i><!--[if IE 7]><img src="' + lb.getMsg('urlRes') + '/images/system/add.png" ><![endif]--></a>'
                + ' <a id="views_#{location_id}" href="#" title="' + lb.getMsg('msgDisplayInNewWindow') + '"><i class="fa fa-list-alt fa-2x"></i><!--[if IE 7]><img src="' + lb.getMsg('urlRes') + '/images/system/win2.gif" ><![endif]--></a>'
                + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>'
                );


        this.form = $(params.form);
        this.url = this.form.action;
        this.post_id = $F(this.form.post_id);
        this.cbxGetAddress = $('is_get_address');
        this.locationContent = $('ckeditor_content');
        this.save_content = $('save_content');
        this.cancel_content = $('cancel_content');
        this.container = $(params.container);

        this.geocoder = new google.maps.Geocoder();

        this.ckeditor = new BSA.CKEditorHtml();

        //Event.observe(window, 'load', this.loadMap.bind(this));
        this.form.observe('submit', this.onFormSubmit.bindAsEventListener(this));
        Event.observe(this.save_content, 'click', this.onSaveContent.bindAsEventListener(this));
        Event.observe(this.cancel_content, 'click', this.onCancelContent.bindAsEventListener(this));

        this.loadMap();
    },
    //--------------- Загрузка карты --------------

    loadMap: function ()
    {

        // Create map
        this.map = new google.maps.Map(this.container, {
            center: new google.maps.LatLng(0, 0),
            zoom: 1,
            mapTypeId: google.maps.MapTypeId.SATELLITE,
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

        var options = {
            parameters: {
                action: 'get',
                post_id: this.post_id
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


        json.locations.each(function (location) {
            this.addMarkerToMap(
                    location.location_id,
                    location.latitude,
                    location.longitude,
                    location.description,
                    location.content,
                    location.correction,
                    location.details
                    );
        }.bind(this));

        this.zoomAndCenterMap();

        BSA.Sys.message_clear();
    },
    //------- Добавление координаты (маркера) --------------

    onFormSubmit: function (e)
    {
        Event.stop(e);

        var form = Event.element(e);
        var address = $F(form.location).strip();

        if (address.length == 0)
            return;

        this.geocoder.geocode({address: address}, this.createPoint.bind(this));
    },
    createPoint: function (geocoderResults, status)
    {
        if (!this.checkGeocoderStatus(status)) {
            return;
        }

        var geocoderResult = geocoderResults[0];

        // Create marker
        var options = {
            parameters: {
                action: 'add',
                post_id: this.post_id,
                description: geocoderResult.formatted_address,
                latitude: geocoderResult.geometry.location.lat(),
                longitude: geocoderResult.geometry.location.lng()
            },
            onSuccess: this.createPointSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },
    onCopyMarker: function (e)
    {
        Event.stop(e);

        var button = Event.element(e);
        button = button.up("a");
        var location_id = button.getAttribute('location_id');

        if (!confirm(lb.getMsg('msgCopyCoordinate') + '?')) {
            return;
        }

        var options = {
            parameters: {
                action: 'add',
                post_id: this.post_id,
                latitude: this.markers.get(location_id).lat,
                longitude: this.markers.get(location_id).lng,
                description: '(копия) ' + this.markers.get(location_id).desc,
                content: this.markers.get(location_id).content
            },
            onSuccess: this.createPointSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },
    createPointSuccess: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id == 0) {
            BSA.Sys.message_write(lb.getMsg('msgErrorAddingLocation'));
            return;
        }

        var marker = this.addMarkerToMap(
                json.location_id,
                json.latitude,
                json.longitude,
                json.description,
                json.content,
                json.correction,
                json.details
                );

        google.maps.event.trigger(marker, 'click');

        this.zoomAndCenterMap();
    },
    //------- Перемещение координаты (маркера) --------------

    dragComplete: function (marker)
    {
        var point = marker.getPosition();

        var value = this.cbxGetAddress.checked;
        if (value) {
            // Сохраним значение кода координаты
            this.location_id = marker.location_id;

            // Определим адрес по координатам точки
            this.geocoder.geocode({location: point}, this.getAddress.bind(this));
        } else {
            var options = {
                parameters: {
                    action: 'move',
                    post_id: this.post_id,
                    location_id: marker.location_id,
                    latitude: point.lat(),
                    longitude: point.lng()
                },
                onSuccess: this.onDragCompleteSuccess.bind(this)
            }

            new Ajax.Request(this.url, options);
        }
    },
    getAddress: function (geocoderResults, status) {

        if (!this.checkGeocoderStatus(status)) {
            return;
        }

        var geocoderResult = geocoderResults[0];

        var options = {
            parameters: {
                action: 'move',
                post_id: this.post_id,
                location_id: this.location_id,
                description: geocoderResult.formatted_address,
                latitude: geocoderResult.geometry.location.lat(),
                longitude: geocoderResult.geometry.location.lng()
            },
            onSuccess: this.onDragCompleteSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },
    onDragCompleteSuccess: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id && this.hasMarker(json.location_id)) {
            var point = new google.maps.LatLng(json.latitude, json.longitude);

            var marker = this.addMarkerToMap(
                    json.location_id,
                    json.latitude,
                    json.longitude,
                    json.description,
                    json.content,
                    json.correction,
                    json.details
                    );
            google.maps.event.trigger(marker, 'click');

            this.zoomAndCenterMap();
        } else {
            BSA.Sys.message_write(lb.getMsg('msgErrorMoveLocation'));
        }
    },
    //------- Удаление координаты (маркера) --------------

    onRemoveMarker: function (e)
    {
        Event.stop(e);

        var button = Event.element(e);
        button = button.up("a");
        var location_id = button.getAttribute('location_id');

        if (!confirm(lb.getMsg('msgDeleteCoordinate') + '?')) {
            return;
        }

        var options = {
            parameters: {
                action: 'delete',
                post_id: this.post_id,
                location_id: location_id
            },
            onSuccess: this.onRemoveMarkerSuccess.bind(this)
        };

        new Ajax.Request(this.url, options);
    },
    onRemoveMarkerSuccess: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id) {
            this.removeMarkerFromMap(json.location_id);
        }
    },
    //------- Редактирование описания координаты (маркера) --------------

    onEditDescription: function (e)
    {

        var description = Event.element(e);
        var location_id = description.getAttribute('location_id');
        var options = null;
        //---------------------------------------------------------

        // Определим тип броузера, если IE
        // то будем работать с окном prompt, если нет
        // то используем Ajax.InPlaceEditor
        if (Prototype.Browser.IE)
        {

            var title = this.markers.get(location_id).desc;
            var comment = prompt(lb.getMsg('msgDescriptionCoordinate'), title).escapeHTML();

            if (comment == title) {
                return;
            }
            if (comment == null) {
                return;
            }

            // Если был изменен комментарий к изображению, то
            // передадим новый комментарий на сервер
            options = {
                method: 'post',
                parameters: {
                    action: 'description',
                    post_id: this.post_id,
                    location_id: location_id,
                    description: comment
                },
                onSuccess: this.onEditDescriptionComplete.bind(this),
                onFailure: this.onEditDescriptionFailure.bind(this)
            }

            //BSA.Sys.message_write(this.msgUpdatingCommentImage);
            new Ajax.Request(this.url, options);
        } else {

            options = {
                cancelControl: 'false',
                okText: 'OK',
                cancelText: lb.getMsg('msgCancel'),
                savingText: lb.getMsg('msgSaving'),
                clickToEditText: lb.getMsg('msgClickToEdit'),
                //formId: location_id,
                //formClassName: 'myfrm',
                callback: function (form, value) {
                    return 'action=description&post_id=' + this.post_id + '&location_id=' + location_id + '&description=' + value;
                }.bind(this),
                onEnterEditMode: function (form, value) {
                }.bind(this),
                onLeaveEditMode: function (form, value) {
                }.bind(this),
                onComplete: this.onEditDescriptionComplete.bind(this),
                onFailure: this.onEditDescriptionFailure.bind(this)
            }

            // Создадим обьект редактирования описания координаты
            var inPlaceEditor = new Ajax.InPlaceEditor('description_' + location_id, this.url, options);
            if (!this.inPlaceEditors.get(location_id)) {
                this.inPlaceEditors.set(location_id, inPlaceEditor);
            }
        }

    },
    onEditDescriptionComplete: function (transport)
    {
        if (!transport) {
            return;
        }
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id && this.hasMarker(json.location_id)) {

            var marker = this.addMarkerToMap(
                    json.location_id,
                    json.latitude,
                    json.longitude,
                    json.description,
                    json.content,
                    json.correction,
                    json.details
                    );
            google.maps.event.trigger(marker, 'click');

            this.zoomAndCenterMap();


        } else {
            BSA.Sys.message_write(lb.getMsg('msgErrorEditeDescription'));
            return;
        }
    },
    onEditDescriptionFailure: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id === 0) {
            BSA.Sys.message_write(json.error);
        }
    },
    //------- Редактирование координаты (маркера) --------------

    onCancelContent: function (e)
    {
        this.ckeditor.removeEditorForAjax('ckeditor_content');
        var location_content = $('location-content');
        location_content.hide();
    },
    onSaveContent: function (e)
    {
        var options;
        var ckeditor_content = this.ckeditor.GetContent('ckeditor_content');
        var location_content = $('location-content');
        var location_id = location_content.getAttribute('location_id');
        var location_edit = location_content.getAttribute('edit');
        this.ckeditor.removeEditorForAjax('ckeditor_content');
        location_content.hide();

        if (location_edit == 'content') {
            options = {
                parameters: {
                    action: 'content',
                    post_id: this.post_id,
                    location_id: location_id,
                    content: ckeditor_content
                },
                onSuccess: this.onEditContentSuccess.bind(this)
            }


        } else if (location_edit == 'details') {
            options = {
                parameters: {
                    action: 'set_details',
                    post_id: this.post_id,
                    location_id: location_id,
                    details: ckeditor_content
                },
                onSuccess: this.onEditDetailsSuccess.bind(this)
            }
        }

        new Ajax.Request(this.url, options);
    },
    //------- Редактирование содержания координаты (маркера) --------------

    onEditContent: function (e)
    {

        Event.stop(e);

        var content = Event.element(e);
        content = content.up("a");
        var location_id = content.getAttribute('location_id');
        var location_content = $('location-content');

        location_content.setAttribute('location_id', location_id);
        location_content.setAttribute('edit', 'content');
        location_content.show();

        var config = {
            toolbar: 'Min'
        }

        this.ckeditor.createEditorForAjax('ckeditor_content', config, this.markers.get(location_id).content);
    },
    onEditContentSuccess: function (transport)
    {
        if (!transport) {
            return;
        }
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id && this.hasMarker(json.location_id)) {

            var marker = this.addMarkerToMap(
                    json.location_id,
                    json.latitude,
                    json.longitude,
                    json.description,
                    json.content,
                    json.correction,
                    json.details
                    );
            google.maps.event.trigger(marker, 'click');

            this.zoomAndCenterMap();
        } else {
            BSA.Sys.message_write(lb.getMsg('msgErrorEditContent'));
            return;
        }
    },
    onEditContentFailure: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id === 0) {
            BSA.Sys.message_write(json.error);
        }
    },
    //------- Редактирование подробной инф. координаты (маркера) --------------

    onEditDetails: function (e)
    {

        Event.stop(e);

        var details = Event.element(e);
        details = details.up("a");
        var location_id = details.getAttribute('location_id');

        var options = {
            parameters: {
                action: 'get_details',
                post_id: this.post_id,
                location_id: location_id
            },
            onSuccess: this.onGetDetailsSuccess.bind(this)
        }

        new Ajax.Request(this.url, options);
    },
    onGetDetailsSuccess: function (transport)
    {
        if (!transport) {
            return;
        }
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id && this.hasMarker(json.location_id)) {

            var location_content = $('location-content');
            if (!json.details) {
                json.details = '';
            }

            location_content.setAttribute('location_id', json.location_id);
            location_content.setAttribute('edit', 'details');
            location_content.show();

            var config = {
                toolbar: 'Medium'
            }

            this.ckeditor.createEditorForAjax('ckeditor_content', config, json.details);
        } else {
            this.onGetDetailsFailure(transport);
            return;
        }
    },
    onGetDetailsFailure: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        BSA.Sys.message_write(json.error);
    },
    onEditDetailsSuccess: function (transport)
    {
        if (!transport) {
            return;
        }
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id && this.hasMarker(json.location_id)) {

            var marker = this.addMarkerToMap(
                    json.location_id,
                    json.latitude,
                    json.longitude,
                    json.description,
                    json.content,
                    json.correction,
                    json.details
                    );
            google.maps.event.trigger(marker, 'click');

            this.zoomAndCenterMap();
        } else {
            BSA.Sys.message_write(lb.getMsg('msgErrorEditContent'));
            return;
        }
    },
    onEditDetailsFailure: function (transport)
    {
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id === 0) {
            BSA.Sys.message_write(json.error);
        }
    },
    //---------------- Управление маркерами -----------------

    addMarkerToMap: function (id, lat, lng, desc, cont, correction, details)
    {
        var self = this;
        //-------------------------
        this.removeMarkerFromMap(id);

        var mapsMarker = new google.maps.Marker({
            map: this.map,
            position: new google.maps.LatLng(lat, lng),
            draggable: true,
            title: desc}
        );

        this.markers.set(id, mapsMarker);
        this.markers.get(id).location_id = id;
        this.markers.get(id).lat = lat;
        this.markers.get(id).lng = lng;
        this.markers.get(id).desc = desc;
        this.markers.get(id).content = cont;
        this.markers.get(id).details = details;

        google.maps.event.addListener(this.markers.get(id), 'dragend', function () {
            self.dragComplete(this);
        });
        google.maps.event.addListener(this.markers.get(id), 'dragstart', function () {
            self.closeInfoWindow();
        });

        google.maps.event.addListener(this.markers.get(id), 'click', function () {
            self.clickMarker(this);
        });

        // Добавим ссылку на детальную информацию, если она есть
        if (details) {
            details = '<a href="' + details + '">' + lb.getMsg('msgDetails') + '</a>';
        }

//        var corr = '';
//        for (var i = 0; i < correction; i++) {
//            corr += '<br />';
//        }


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

        //var tdContent = $();

        // Найдем обьекты
        var location_delete = node.getElementsBySelector('a#delete_' + id)[0];
        var location_edit = node.getElementsBySelector('a#edit_' + id)[0];
        var location_details = node.getElementsBySelector('a#details_' + id)[0];
        var location_copy = node.getElementsBySelector('a#copy_' + id)[0];
        var location_views = node.getElementsBySelector('a#views_' + id)[0];

        var description = node.getElementsBySelector('div#description_' + id)[0];
        var content = node.getElementsBySelector('td#content_' + id)[0];

        // Установим атрибуты обьектов
        location_delete.setAttribute('location_id', id);
        location_edit.setAttribute('location_id', id);
        location_details.setAttribute('location_id', id);
        location_copy.setAttribute('location_id', id);
        location_views.setAttribute('location_id', id);
        description.setAttribute('location_id', id);
        content.setAttribute('location_id', id);

        // Установим события обьектов
        location_delete.observe('click', this.onRemoveMarker.bindAsEventListener(this));
        location_edit.observe('click', this.onEditContent.bindAsEventListener(this));
        location_details.observe('click', this.onEditDetails.bindAsEventListener(this));
        location_copy.observe('click', this.onCopyMarker.bindAsEventListener(this));
        location_views.observe('click', this.onOpenViewsWin.bindAsEventListener(this));
        description.observe('dblclick', this.onEditDescription.bindAsEventListener(this));
        //content.observe('dblclick', this.onEditContent.bindAsEventListener(this));

        return this.markers.get(id);
    },
    removeMarkerFromMap: function (location_id)
    {
        if (!this.hasMarker(location_id)) {
            return;
        }

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
        var location_id = marker.location_id;
        this.location_id = location_id;
        // Удалим соответсвующий редактор
        if (this.inPlaceEditors.get(location_id)) {
            this.inPlaceEditors.get(location_id).destroy();
            this.inPlaceEditors.set(location_id, null);
        }

        // Open info windows
        this.infoWindow.setContent(marker.node);
        this.infoWindow.open(marker.getMap(), marker);

        // Установим событие, после того как появяться элементы в DOM
        this.idTimeout = window.setTimeout(function () {

            // Изменим содержимое окна
            this.modifyMarkerImages();

            // Изменить события ссылок содержания маркера
            this.modifyMarkerLinks();

            window.clearTimeout(this.idTimeout);
        }.bind(this), 500);
    },
    // Изменить содержание маркера
    //
    // - пр. преобразовать тег <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //   в теги -> <a href="/upload/users/user1/images/bsa.jpg"" rel="lightbox[location]">
    //               <img alt="" src="/upload/users/user1/.thumbs/images/bsa.jpg" style="width: 100px; height: 100px; ">
    //             </a>
    modifyMarkerImages: function ()
    {
        // Обьект маркера
        var content = $('content_' + this.location_id);
        var scr = '';

        // Проверим обертку элемента img, если уже есть, то выйдем из ф-ии
        var rel_lightbox = content.select('[rel="lightbox[location]"]');
        if (rel_lightbox.size() > 0) {
            return;
        }

        // Установим ссылку на изображение
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
        //var marker = this.markers.get(this.location_id);
        //this.markers.get(id).lng = lng;
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
    //--------- Коррекция окна отображения информации координаты -----------------

    saveCorrectionInfoWindow: function (correction)
    {
        var options = {
            parameters: {
                action: 'correction',
                post_id: this.post_id,
                location_id: this.location_id,
                correction: correction
            },
            onSuccess: this.onCorrectionInfoWindowSuccess.bind(this)

        }

        new Ajax.Request(this.url, options);

        BSA.Sys.message_write(lb.getMsg('msgSaveCorrectionInfoWindow'));

    },
    onCorrectionInfoWindowSuccess: function (transport)
    {
        if (!transport) {
            return;
        }
        var json = BSA.Sys.getJsonResponse(transport, true);

        if (json.location_id && this.hasMarker(json.location_id)) {
            BSA.Sys.message_clear();
        } else {
            BSA.Sys.message_write(lb.getMsg('msgErrorSaveCorrectionInfoWindow'));
            return;
        }
    },

    //-------------- Работа с окнами ---------------

    // Открыть содержимое координаты в отдельном окне
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
                + '</div>'
        win.getContent().update(win_content);
        win.showCenter();

        marker.closeInfoWindow();
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
    checkGeocoderStatus: function (status)
    {
        if (status != google.maps.GeocoderStatus.OK) {
            // something went wrong:
            var msg = '';
            switch (status) {
                // This GeocoderRequest was invalid.
                case google.maps.GeocoderStatus.INVALID_REQUEST:
                    msg = lb.getMsg('msgErrorGeocoderRequest');
                    break;
                    // The webpage has gone over the requests limit in too short a period of time.
                case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:
                    msg = lb.getMsg('msgErrorOverRequestsTimeLimit');
                    break;
                    // The webpage is not allowed to use the geocoder.    
                case google.maps.GeocoderStatus.REQUEST_DENIED:
                    msg = lb.getMsg('msgErrorDenyAccessGeocoder');
                    break;
                case google.maps.GeocoderStatus.ZERO_RESULTS:
                    msg = lb.getMsg('msgErrorNoResultForGeocoderRequest');
                    break;
                case google.maps.GeocoderStatus.UNKNOWN_ERROR:
                    msg = lb.getMsg('msgUnknownServerError');
                    break;
                    // There was a problem contacting the Google servers.
                case google.maps.GeocoderStatus.ERROR:
                    msg = lb.getMsg('msgErrorContactingGoogleServers');
                    break;
                default:
                    msg = lb.getMsg('msgUnknownServerError');
            }
            BSA.Sys.message_write(msg);
        }

        return (status === google.maps.GeocoderStatus.OK);
    }

});

// Ф-ия, выполняемая при загрузки окна броузера
// создаются обьекты класса, экземпляры их
// заносяться в список экземпляров
// пр. $H(BlogLocationManager: [new BlogLocationManager(param1), ... ,new BlogLocationManager(paramN)])
BSA.BlogLocationManager.RegRunOnLoad = function () {
    // Получим параметры для создания обьекта
    var params = scriptParams.get('BlogLocationManager');
    // Ф-ия создания обьектов по их параметрам
    var createObject = function (param) {
        var locationManager = scriptInstances.get('BlogLocationManager');
        if (locationManager) {
            locationManager.push(new BSA.BlogLocationManager(param));
        } else {
            scriptInstances.set('BlogLocationManager', [new BSA.BlogLocationManager(param)]);
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
runOnLoad(BSA.BlogLocationManager.RegRunOnLoad);