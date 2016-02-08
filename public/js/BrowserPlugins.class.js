// JavaScript Document
// class: allplugins
// this script detect all plugins in the page
// Dixn Santiesteban Feria, CUBA
// email: dixan_sant@yahoo.es
// ----------------------------
// SVG Viewer: svg
// Shockwave Director: director
// Shockwave Flash: flash
// RealPlayer: realplayer
// QuickTime: quicktime
// Windows Media Player: wmp
// Acrobat Reader: areader
// Java: java


BSA.allPlugins = Class.create({
    
    initialize : function()
    {
        var ie  = (navigator.userAgent.toLowerCase().indexOf("msie") != -1);
        var nplu = ["svg","director","flash","realplayer","quicktime","wmp","areader"];


        var plu = (ie)?["Adobe.SVGCtl",
        "SWCtl.SWCtl.1",
        "ShockwaveFlash.ShockwaveFlash.1",
        "rmocx.RealPlayer G2 Control.1",
        "QuickTimeCheckObject.QuickTimeCheck.1",
        "MediaPlayer.MediaPlayer.1",
        "PDF.PdfCtrl.5"]:["image/svg-xml",
        "application/x-director",
        "application/x-shockwave-flash",
        "audio/x-pn-realaudio-plugin",
        "video/quicktime",
        "application/x-mplayer2",
        "application/pdf"]
	
	
        for (var t=0; t<plu.length; t++) this[nplu[t]]=(ie)?this.detectIE(plu[t]):this.detect(plu[t]);
    
        this.java = navigator.javaEnabled();
    
    },

    detect: function(id) { 
        navs = ""; 
        for (var i=0;i<navigator.mimeTypes.length;i++) 
            navs += navigator.mimeTypes[i].type.toLowerCase();
        if (navs.indexOf(id) != -1) if (navigator.mimeTypes[id].enabledPlugin != null) return true;
        return false;
    },

    detectIE: function(id) { 
        result = false; 
        document.write('<SCRIPT LANGUAGE="VBScript">\n on error resume next \n result = IsObject(CreateObject("' + id + '"))</SCRIPT>\n'); 
        return result; 
    }

});
