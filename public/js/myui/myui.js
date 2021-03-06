/**
 * MYUI, version 1.0
 *
 * Dual licensed under the MIT and GPL licenses.
 *
 * Copyright 2009 Pablo Aravena, all rights reserved.
 * http://pabloaravena.info
 *
 */
var MyUI = {
    Version: '1.0',
    REQUIRED_PROTOTYPE: '1.6',
    requireLibrary: function (libraryName) {
        try {
            // inserting via DOM fails in Safari 2.0, so brute force approach
            document.write('<script type="text/javascript" src="' + libraryName + '"><\/script>');
        } catch (e) {
            // for xhtml+xml served content, fall back to DOM methods
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = libraryName;
            document.getElementsByTagName('head')[0].appendChild(script);
        }
    },
    requireCSS: function (cssDefinitionFile) {
        try {
            // inserting via DOM fails in Safari 2.0, so brute force approach
            document.write('<link type="text/css" href="' + cssDefinitionFile + '" rel="stylesheet">');
        } catch (e) {
            // for xhtml+xml served content, fall back to DOM methods
            var cssDef = document.createElement('link');
            cssDef.type = 'text/css';
            cssDef.href = cssDefinitionFile;
            document.getElementsByTagName('head')[0].appendChild(cssDef);
        }
    },
    load: function () {
        function convertVersionString(versionString) {
            var v = versionString.replace(/_.*|\./g, '');
            v = parseInt(v + '0'.times(4 - v.length));
            return versionString.indexOf('_') > -1 ? v - 1 : v;
        }

        if ((typeof Prototype == 'undefined') ||
                (typeof Element == 'undefined') ||
                (typeof Element.Methods == 'undefined') ||
                (convertVersionString(Prototype.Version) <
                        convertVersionString(MyUI.REQUIRED_PROTOTYPE)))
            throw("MyUI requires the Prototype JavaScript framework >= " +
                    MyUI.REQUIRED_PROTOTYPE);

        var js = /myui\.js(\?.*)?$/;
        $$('head script[src]').findAll(function (s) {
            return s.src.match(js);
        }).each(function (s) {
            var path = s.src.replace(js, '');
            var includes = s.src.match(/\?.*load=([a-z,]*)/);
            (includes ? includes[1] : 'Utilities,i18n,ToolTip,TextField,Date,DatePicker,TableGrid,KeyTable,controls,Autocompleter,ComboBox').split(',').each(
                    function (include) {
                        MyUI.requireLibrary(path + include + '.js');
                    });
            path = path.replace('scripts', 'css');
            'myui,ToolTip,TextField,DatePicker,TableGrid,Autocompleter'.split(',').each(
                    function (include) {
                        MyUI.requireCSS(path + include + '.css');
                    });
        });
    }
};
//Event.observe(document, 'dom:loaded', MyUI.load);
MyUI.load();
var MY = {};