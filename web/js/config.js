// http://localhost/xy/foo => http://localhost/xy
function baseUrl(url){
    return url.substring(0, location.href.lastIndexOf('/'));
}

Ext.HTTP_SERVICE_URL = baseUrl(location.href);

// check if given url starts with localhost
function isLocalUrl(url){
    return (url.substring(0,16) == 'http://localhost');
}

// load Ext JS needed 1px x 1px space filling image 's.gif'
// dependend on url
if(isLocalUrl(Ext.HTTP_SERVICE_URL)) {
    Ext.BLANK_IMAGE_URL  = 'js/lib/extjs/resources/images/default/s.gif';
} else {
    Ext.BLANK_IMAGE_URL  = 'http://extjs.cachefly.net/ext-3.3.1/resources/images/default/s.gif';
}

//Ext.useShims = true;
Ext.ns('App');
App.PREFIX = 'DBpedia';