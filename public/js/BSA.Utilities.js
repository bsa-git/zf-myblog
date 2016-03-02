/**
 * BSA.Utilities - object utilities
 *
 * This object allows:
 *  - Refer to edit messages under the name of the registered user
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

BSA.Utilities = {
    
    // Get the password to access the site
    loginUserToEditMessage: function(params)
    {
        var self = this;
        // Установим URL контроллера
        var url = lb.getMsg('urlBase') + '/admin/utility/userpassword';
        
        //--------------------------------
        
        //Очистим сообщение об ошибке
        BSA.Sys.messagebox_clear();

        new Ajax.Request(url, {
            parameters: params,
            onSuccess: function(response) {
                
                try {
                    var json = BSA.Sys.getJsonResponse(response, true);
                    
                    // Проверим есть ли ошибки
                    if (! json.class_message) {// OK
                        var msg = lb.getMsg('msgDoYouToEditMessageForUser', params);
                        if (confirm(msg)){
                            // Перейдем на редактирования сообщения под пользователем 'username''
                            var password = json.password;
                            var username = params.username;
                            var redirect = lb.getMsg('urlBase') + '/blogmanager/preview?id=' + params.post_id;
                            var urlLogin = lb.getMsg('urlBase') + '/admin/user/login?username=' + 
                            username + '&password=' + password + '&redirect=' + redirect;
                            window.location.replace(urlLogin);
                        }
                    }

                } catch (ex) {
                    if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                        self.onFailure(ex.name + ": " + ex.message);
                    }

                } finally {

                }
            },
            onFailure : function(transport) {
                var errText = transport.responseText;
                var msgs = [lb.getMsg('errRetrieveDataFromUrl'), errText];
                BSA.Sys.messagebox_write('warning', msgs);
                self.onFailure(lb.getMsg('errRetrieveDataFromUrl'));
            }
        });
    },
    
    _getHTTPObject: function(){ 
        //Create a boolean variable to check for a valid Internet Explorer instance.
        var xmlhttp = false;
        //Check if we are using IE.
        try {
            //If the Javascript version is greater than 5.
            xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            //If not, then use the older active x object.
            try {
                //If we are using Internet Explorer.
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                //Else we must be using a non-IE browser.
                xmlhttp = false;
            }
        }
        //If we are using a non-IE browser, create a javascript instance of the object.
        if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
            xmlhttp = new XMLHttpRequest();
        }
 
        return xmlhttp;
    },
    
    UrlExists: function(url)
    {
        var http_check = this._getHTTPObject();
        http_check.open('HEAD', url, false);
        http_check.send();
        return http_check.status!=404;
    },
    
    base64_encode: function(data) {
        // http://kevin.vanzonneveld.net
        // +   original by: Tyler Akins (http://rumkin.com)
        // +   improved by: Bayron Guevara
        // +   improved by: Thunder.m
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Pellentesque Malesuada
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   improved by: Rafał Kukawski (http://kukawski.pl)
        // *     example 1: base64_encode('Kevin van Zonneveld');
        // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
        // mozilla has this native
        // - but breaks in 2.0.0.12!
        //if (typeof this.window['btoa'] == 'function') {
        //    return btoa(data);
        //}
        var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = "",
        tmp_arr = [];

        if (!data) {
            return data;
        }

        do { // pack three octets into four hexets
            o1 = data.charCodeAt(i++);
            o2 = data.charCodeAt(i++);
            o3 = data.charCodeAt(i++);

            bits = o1 << 16 | o2 << 8 | o3;

            h1 = bits >> 18 & 0x3f;
            h2 = bits >> 12 & 0x3f;
            h3 = bits >> 6 & 0x3f;
            h4 = bits & 0x3f;

            // use hexets to index into b64, and append result to encoded string
            tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
        } while (i < data.length);

        enc = tmp_arr.join('');

        var r = data.length % 3;

        return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);

    },
    
    base64_decode: function(data) {
        // http://kevin.vanzonneveld.net
        // +   original by: Tyler Akins (http://rumkin.com)
        // +   improved by: Thunder.m
        // +      input by: Aman Gupta
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +   bugfixed by: Onno Marsman
        // +   bugfixed by: Pellentesque Malesuada
        // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // +      input by: Brett Zamir (http://brett-zamir.me)
        // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
        // *     returns 1: 'Kevin van Zonneveld'
        // mozilla has this native
        // - but breaks in 2.0.0.12!
        //if (typeof this.window['atob'] == 'function') {
        //    return atob(data);
        //}
        var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = "",
        tmp_arr = [];

        if (!data) {
            return data;
        }

        data += '';

        do { // unpack four hexets into three octets using index points in b64
            h1 = b64.indexOf(data.charAt(i++));
            h2 = b64.indexOf(data.charAt(i++));
            h3 = b64.indexOf(data.charAt(i++));
            h4 = b64.indexOf(data.charAt(i++));

            bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

            o1 = bits >> 16 & 0xff;
            o2 = bits >> 8 & 0xff;
            o3 = bits & 0xff;

            if (h3 == 64) {
                tmp_arr[ac++] = String.fromCharCode(o1);
            } else if (h4 == 64) {
                tmp_arr[ac++] = String.fromCharCode(o1, o2);
            } else {
                tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
            }
        } while (i < data.length);

        dec = tmp_arr.join('');

        return dec;
    },
    
    // Error handling
    onFailure : function(message) {
        var msgs;
        if(message.class_message){
            //Очистим сообщение об ошибке
            BSA.Sys.messagebox_clear();
            msgs = message.messages;
            BSA.Sys.messagebox_write(message.class_message, msgs);
        }else{
            BSA.Sys.err_message_write(message);
        }

    }
}