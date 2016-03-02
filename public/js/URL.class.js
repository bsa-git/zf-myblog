/**
 * Class to manipulate URL.
 * With this class you can:
 * - parse an URL and gets its components;
 * - modify URL components;
 * - add or remove params of query string;
 * - build a new URL with defined components.
 *
 * Copyright (C) 2011  Rubens Takigti Ribeiro
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Lesser General Public License for more details.
 *
 * @author Rubens Takiguti Ribeiro
 * @version 1.0 2011-05-24
 * @license LGPL 3 http://www.gnu.org/licenses/lgpl-3.0.txt
 * @see RFC-1738 http://www.faqs.org/rfcs/rfc1738.html
 */


/**
 * Class constructor: parse an URL and keep its components
 * @param string url URL to be parsed (optional). Default: no string.
 */
function URL(url) {

    /**
     * Parsed URL
     * @var string
     */
    this.url = null;

    /**
     * Scheme component of URL
     * @var string
     */
    this.scheme = null;

    /**
     * User component of URL
     * @var string
     */
    this.user = null;

    /**
     * Password component of URL
     * @var string
     */
    this.pass = null;
    
    /**
     * Host component of URL
     * @var string
     */
    this.host = null;
    
    /**
     * Port component of URL
     * @var int
     */
    this.port = null;
    
    /**
     * Path component of URL
     * @var string
     */
    this.path = null;
    
    /**
     * Query component of URL (without "?")
     * @var string
     */
    this.query = null;
    
    /**
     * Fragment component of URL (without "#")
     * @var string
     */
    this.fragment = null;
    
    /**
     * Array of objects (with name and value) from query string
     * @var Array
     */
    this.params = new Array();
    
    // Constructor instruction
    if (url != undefined) {
        this.parseURL(url);
    }
};


/**
 * Parse an URL and keep its components at current object
 * @param string url
 * @return bool
 */
URL.prototype.parseURL = function(url) {
    var exp = /^([a-z0-9+\.\-]+):\/\/(?:(?:([^:]*):([^@]*))@)?((?:[A-Za-z0-9_\.\-])+)(?::([0-9]+))?([^?]+)?(?:\?([^#]*))?(?:\#(.*))?$/;
    var matches = exp.exec(url);
    if (!matches) {
        return false;
    }

    this.url = url;
    this.scheme = matches[1];
    if (matches[2] != null) {
        this.user = decodeURIComponent(matches[2]);
        this.pass = decodeURIComponent(matches[3]);
    }
    this.host = matches[4];
    if (matches[5] != null) {
        this.port = parseInt(matches[5]);
    }
    if (matches[6] != null) {
        this.path = decodeURIComponent(matches[6]);
    }
    if (matches[7] != null) {
        this.query = matches[7];
        this.parseQuery(this.query);
    }
    if (matches[8] != null) {
        this.fragment = decodeURIComponent(matches[8]);
    }

    return true;
};


/**
 * Parse a Query String and keep its params at current object
 * @param string query
 * @return bool
 */
URL.prototype.parseQuery = function(query) {

    // Normalize query delimiter
    query = query.replace("&amp;", "&");
    query = query.replace("&#38;", "&");
    query = query.replace("&#x26;", "&");
    query = query.replace("&#X26;", "&");
    
    // Split
    var parts = query.split("&");
    
    var part = "";
    var pos = "";
    var param = null;
    for(var i = 0; i < parts.length; i++){
        //    for (var i in parts) {
        part = parts[i];
        if (part != "") {
            pos = part.indexOf("=");
            
            // Creating object with name and value
            if (pos >= 0) {
                param = {
                    name: part.substr(0, pos),
                    value: decodeURIComponent(part.substr(pos + 1))
                };
            } else {
                param = {
                    name: part,
                    value: null
                };
            }
            this.params.push(param);
        }
    }
    return true;
};


/**
 * Set the value of a URL component.
 * @throw Exception when try to modify "url" or "query" components.
 * @param string component (scheme, user, pass, host, port, path or fragment)
 * @param mixed value (use null to "unset" the value)
 * @return this
 */
URL.prototype.set = function(component, value) {
    switch (component) {
        case "url":
            throw "Can not set 'url' componet. Use URL.parseURL instead.";
            break;
        case "query":
            throw "Can not set 'query' componet. Use URL.addParam, URL.removeParam or URL.clearParams instead.";
            break;
        case "scheme":
        case "user":
        case "pass":
        case "host":
        case "path":
        case "fragment":
            if (value == null) {
                var jscode = "this." + component + " = null;";
            } else {
                var jscode = "this." + component + " = value.toString();";
            }
            eval(jscode);
            break;
        case "port":
            this.port = parseInt(value);
            break;
    }
    return this;
};


/**
 * Add param to params property.
 * It does not modify query property (@see URL.buildQuery).
 * @param string name Param name
 * @param string value Param value
 * @return this
 */
URL.prototype.addParam = function(name, value) {
    var param = {
        name: name,
        value: value
    };
    this.params.push(param);
    return this;
};


/**
 * Remove param from params property.
 * It does not modify query property (@see URL.buildQuery).
 * @param string name Param name (might be a scalar param, an array name or an array position)
 * @return this
 */
URL.prototype.removeParam = function(name) {
    var params2 = new Array();
    for (var i in this.params) {
        var param = this.params[i];
        if (param.name == name) {
        //void
        } else {
            pos = param.name.indexOf("[");
            if (pos >= 0) {
                var array_param_name = param.name.substr(0, pos);
                if (array_param_name == name) {
                //void
                } else {
                    params2.push(param);
                }
            } else {
                params2.push(param);
            }
        }
    }
    this.params = params2;
    return this;
};


/**
 * Remove all params from params property.
 * It does not modify query property (@see URL.buildQuery).
 * @return this
 */
URL.prototype.clearParams = function() {
    this.params = new Array();
    return this;
};


/**
 * Check whether a param is defined in query.
 * @param string name Param name
 * @return bool
 */
URL.prototype.hasParam = function(name) {
    var param = null;
    for (var i in this.params) {
        param = this.params[i];
        if (param.name == name) {
            return true;
        } else {
            pos = param.name.indexOf("[");
            if (pos >= 0) {
                var array_param_name = param.name.substr(param.name, pos);
                if (array_param_name == name) {
                    return true;
                }
            }
        }
    }
    return false;
};


/**
 * Build query string with data from params property,
 * modify query property and return it.
 * @param string delimiter String used to delimit params (ie "&" or "&amp;"). Default: "&".
 * @return string
 */
URL.prototype.buildQuery = function(delimiter) {
    if (delimiter == undefined) {
        delimiter = "&";
    }

    var params = new Array();
    var str_param = "";
    var param = null;
    for (var i in this.params) {
        param = this.params[i];
        if (param.value == null) {
            str_param = encodeURIComponent(param.name);
        } else {
            str_param = encodeURIComponent(param.name) + "=" + encodeURIComponent(param.value);
        }
        params.push(str_param);
    }
    this.query = params.join(delimiter);
    return this.query;
};


/**
 * Build URL using current object properties.
 * @param string delimiter String used to delimit params (ie "&" or "&amp;"). Default: "&".
 * @return string
 */
URL.prototype.buildURL = function(delimiter) {
    var str_url = this.scheme + "://";
    if (this.user != null) {
        str_url += encodeURIComponent(this.user) + ":" + encodeURIComponent(this.pass) + "@";
    }
    str_url += this.host;    
    if (this.port != null) {
        str_url += ":" + this.port.toString();
    }
    if (this.path != null) {
        str_url += this.path;
    }
    if (this.params.length > 0) {
        str_url += "?" + this.buildQuery(delimiter);
    }
    if (this.fragment != null) {
        str_url += "#" + this.fragment;
    }
    return str_url;
};


URL.prototype.getHTTPObject = function(){ 
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
        } catch (e) {
            //Else we must be using a non-IE browser.
            xmlhttp = false;
        }
    }
    //If we are using a non-IE browser, create a javascript instance of the object.
    if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
};
    
URL.prototype.UrlExists = function(url)
{
    var status;
    try{
        var http_check = this.getHTTPObject();
        http_check.open('HEAD', url, false);
        http_check.send();
        status = http_check.status;
        return status != 404;
    } catch (ex) {
        if (ex instanceof Error) { 
            return false;
        }
    }
};

/**
 * Call URL.buildURL with default param.
 * @return string
 */
URL.prototype.toString = function() {
    return this.buildURL("&");
};

