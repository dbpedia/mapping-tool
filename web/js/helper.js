/**
 * register namespace App
 */
Ext.ns('App');

/**
 * compares two chars a and b
 * for ascending order on
 * Array.sort()
 *
 * @param a string
 * @param b string
 * @return int -1|0|1
 */
App.charOrdAsc = function(a, b){
  a = a.toLowerCase();
  b = b.toLowerCase();
  if (a > b) return 1;
  if (a < b) return -1;
  return 0;
}

/**
 * compares two chars a and b
 * for descending order on
 * Array.sort()
 *
 * @param a string
 * @param b string
 * @return int -1|0|1
 */
App.charOrdDesc = function(a, b){
  a = a.toLowerCase();
  b = b.toLowerCase();
  if (a < b) return 1;
  if (a > b) return -1;
  return 0;
}

App.loadWikipediaTemplate = function (title, wikiTemplateTreeRootNode, wikiTemplateTree){
    //console.log(title);
    var wikiTemplateTreeData = new Array();

    markup = App.getMarkupByWikipediaAjaxRequest(title);
    wikiTemplateTreeData = App.extractPropertiesFromWikiMarkup(markup);
    
    // remove child nodes of root node
    if(wikiTemplateTreeData === undefined||wikiTemplateTreeData==false){
        return;
    }

    while(wikiTemplateTreeRootNode.firstChild){
        wikiTemplateTreeRootNode.removeChild(wikiTemplateTreeRootNode.firstChild);
    }

    for(var i = 0, len = wikiTemplateTreeData.length; i < len; i++) {
        wikiTemplateTreeRootNode.appendChild(
            wikiTemplateTree.getLoader().createNode({
                //text: 'wiki:<b>' + wikiTemplateTreeData[i] + '</b>',
                text:  wikiTemplateTreeData[i],
                value: wikiTemplateTreeData[i],
                label: wikiTemplateTreeData[i],
                name:  wikiTemplateTreeData[i],
                //nodeType: 'node',
                iconCls: 'my-tree-icon-DBpediaTemplateProperty',
                type: 'TemplateProperty',
                leaf: true
            })
        );
    }
    wikiTemplateTree.getRootNode().setText(title);
    wikiTemplateTree.getRootNode().expand();
}







/**
 * etxtract wikipedia properties
 * from a wikipedia template
 */
App.extractPropertiesFromWikiMarkup = function(wikiMarkup){
    var out = new Array();
    
    // abort if no wiki markup is found
    if(!wikiMarkup || wikiMarkup == ''){
        //Ext.Msg.alert('Info', 'no wiki markup found.');
        return false;
    }
    // search for wiki properties
    var matches = wikiMarkup.match(/\{\{\{([A-Za-z\u00C0-\uFFFD_0-9 \-\/]+)(\||\<|\})/g);
    //console.debug(matches);
    
    // abort if no wiki properties found
    if(matches == undefined || matches == null){

/*
        var local_expr= new RegExp("^\\s*#("+redirect_alias+"|redirect)\\s*:?\\s*\\[\\[([^\\]]+)\\]\\]", "gi")      ;
        var redirectPattern = local_expr; ///^\s*#redirect\s*:?\s*\[\[([^\]]+)\]\]/gi;
        var redirectTemplate = redirectPattern.exec(wikiMarkup);
*/
        
        //Ext.Msg.confirm('Info', 'no wiki template properties found');
        
 /*
       if(!redirectTemplate){
*/
            Ext.Msg.alert('Error', 'Could not load wikipedia template.');
            matches = new Array();
/*
        }
*/
        
/*
        if(redirectTemplate && redirectTemplate[2]){
            
            // stop the flow of javascript with a native window
            var box=window.confirm('The Wikipedia template redirects to ' + redirectTemplate[2] + '. Do you want to follow the redirect?');
            if(box==true){
                var box2 = window.confirm('Do you want to adobt the title of the target for your mapping?');
                if(box2 == true){
                var lang_par;
                  if(gup("lang"))lang_par="lang="+gup("lang")+"&";
                    window.location.href = window.location.pathname + "?"+lang_par+"titles=" + encodeURI(redirectTemplate[2].replace(/Template\:/, ''));
                } else {
                    var wikiTemplateTree = Ext.getCmp('wikiTemplateTree');
                    var wikiTemplateTreeRootNode = wikiTemplateTree.root;
                    //Ext.getCmp('templatename').setValue(redirectTemplate[1]);
                    //return redirectTemplate[1].replace(/Template\:/, '');
                    return App.loadWikipediaTemplate(redirectTemplate[2].replace(/Template\:/, ''), wikiTemplateTreeRootNode, wikiTemplateTree);
                }
            }
            return;
        }
*/
    }
    
    if(matches == null){
        matches = new Array();
    }
    
    // filter each property for invalid characters
    // and add it to the output array
    Ext.each(matches, function(){
        var property = this.match(/[A-Za-z\u00C0-\uFFFD_0-9 \-\/]+/g);
        out.push(property[0]);
    });
    
    // remove duplicate properties
    //out = out.unique(out);
    if(out!==undefined && out !== '' && out !== null){
        out = $.unique(out);
    }
    // sort properties in ascending order
    out = out.sortAsc();
    
    return out;
}

App.getMarkupByWikipediaAjaxRequest = function(title) {
    var markup = '';
    $.ajax({
        url: Ext.HTTP_SERVICE_URL + '/api.php',
        async: false,
        dataType: 'json',
        data: {
             'lang' : lang_parameter,
            'titles': 'Template:' + title.replace(new RegExp(template_alias+":", "g"), '').replace(/Template\:/,''),
            'action': 'wikipediaproperties'
        },
        success: function(json){
            if ( json ) {
                if(json.redirect){
                 var box=window.confirm('The Wikipedia template redirects to ' + json.redirect + '. Click OK to follow the redirect.');
                 if(box==true){
                     var box2 = window.confirm('Do you want to adopt the title of the target for your mapping? Click OK to adopt the title.');
                     if(box2 == true){
                          var lang_par;
                          if(gup("lang"))lang_par="lang="+gup("lang")+"&";

                          window.location.href = window.location.pathname + "?"+lang_par+"titles=" + encodeURI( json.redirect);

                     }
                     else
                     {
                           var wikiTemplateTree = Ext.getCmp('wikiTemplateTree');
                           var wikiTemplateTreeRootNode = wikiTemplateTree.root;
                           return App.loadWikipediaTemplate(json.redirect.replace(/Template\:/, ''), wikiTemplateTreeRootNode, wikiTemplateTree);



                     }
                 }
                }
                markup = json.templateMarkup;

                return;
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown){
            Ext.Msg.alert('Wikitemplate not loaded');
        }
    });
    return markup;
}

/**
 * Update Tree Node quicktips
 * http://www.sencha.com/forum/archive/index.php/t-39537.html
 * 
 * usage updateqt(n, n.attributes.qtip , qtitletext);
 * B = node
 * C = node.attributes.qt ( new text of quicktip)
 * A = new text for the title of quicktip
 */
function updateqt(B, C, A) {
    if (B.getUI().textNode.setAttributeNS) {
        B.getUI().textNode.setAttributeNS("ext", "qtip", C);
        if (A) {
            B.getUI().textNode.setAttributeNS("ext", "qtitle", A);
        }
    } else {
        B.getUI().textNode.setAttribute("ext:qtip", C);
        if (A) {
            B.getUI().textNode.setAttribute("ext:qtitle", A);
        }
    }
}

function gup ( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}