// enable Ext JS Quick Tips for
// hint boxes on mouseover of nodes
// if node is not valid
Ext.QuickTips.init();

// if Ext JS is completely loaded
// start the app
Ext.onReady(function(){

    // Ext JS store to hold the list of matching template names
    // for the autocompletion field for the Wikipedia templates
    var wikipediaTemplatesStore = new Ext.data.JsonStore({
         url: Ext.HTTP_SERVICE_URL + '/api.php'
        ,baseParams: {
            action: 'autocomplete',
            lang:   lang_parameter
        }
        ,totalProperty: 'total'
        ,fields: [
            {name:'site', mapping:'site'}
        ]
        ,root: 'data'
        ,sortInfo:{
            field: "site",
            direction: "ASC"
        }
    }); // eof wikipediaTemplatesStore

    // Ext JS autocompletion field for the Wikipedia Templates
    var autoCompleteTemplates = new Ext.form.ComboBox({
           id: 'templatename'
          ,tpl: '<tpl for="."><div ext:qtip="{site}" class="x-combo-list-item">{site}</div></tpl>'
          ,store: wikipediaTemplatesStore
          ,minChars: 1
          ,fieldLabel: 'Site:'
          ,displayField: 'site'
          ,loadingText: 'templates loading'
          ,forceSelection: false
          ,lazyRender: true
          //,typeAhead:true
          ,valueNotFoundText: 'no template found...'
          ,mode: 'remote'
          ,triggerAction: 'all'
          ,emptyText: 'Infobox example'
          ,enableKeyEvents: true
          ,width: 300
          ,listeners: {
              keyup: function(elem, evnt){
                  // if ENTER key is pressed start
                  // the AJAX request to retrieve matching
                  // templates
                  if(evnt.getKey() == evnt.ENTER){
                      loadTemplates(Ext.getCmp('templatename').getValue());
                  }
              }
              ,scope: this
          }
    }); // eof autoCompleteTemplates

    // tweaking the Ajax call for data loading
    // when pulling the ontology from DBpedia
    // to show the status of the operation
    // in the status bar of the Ext JS window
    var maskingAjax = new Ext.data.Connection({
        listeners: {
            'beforerequest': {
                fn: function(con,opt) {
                    Ext.getCmp('basic-statusbar').register();
                },
                scope: this
            },
            'requestcomplete': {
                fn: function(con,res,opt) {
                    Ext.getCmp('basic-statusbar').unregister();
                },
                scope: this
            },
            'requestexception': {
                fn: function(con,res,opt) {
                    Ext.getCmp('basic-statusbar').unregister();
                },
                scope: this
            }
        }
    }); //eof maskingAjax

    // Ext JS tree loader for the ontology widget
    var treeLoader = new Ext.tree.TreeLoader({
        dataUrl: Ext.HTTP_SERVICE_URL + '/api.php',
        baseParams: {
            action: 'ontology',
            lang: lang_parameter,
            load: 'initial'
        }
    }); // eof treeLoader

    // root node of the ontology widget tree
    var rootNode = new Ext.tree.AsyncTreeNode({
         text: 'owl:Thing'
        ,label: 'owl:Thing'
        ,id: 1
        ,iconCls: 'my-tree-icon-DBpediaOntologyClass'
        ,expanded: true
    }); // eof rootNode

    // ontology widget settings
    var tree = new Ext.tree.TreePanel({
         title: 'Classes'
        ,loader: treeLoader
        ,root: rootNode
        ,border: true
        ,enableDrag: true
        ,anchor: '100%, 50%'
        ,autoScroll: true
        ,layout: 'fit'

        // the top tool bar of the ontology widget
        // containing a remote ajax search box for
        // ontology classes and a button to call
        // a form to create a new class
        ,tbar: [
            'Search: ',
            ' ',
            new Ext.ux.form.SearchField({
                emptyText: 'ontology class',
                onTrigger1Click: function(){
                    this.custSearch();
                    return false;
                },
                onTrigger2Click: function(){
                    this.custSearch();
                    return false;
                },
                custSearch: function (){
                    var v = this.getRawValue();
                    if(v.length > 0 && v.length < 3){
                        Ext.Msg.alert('Info', 'your search query is too short (min 3 characters)');
                        return;
                    }
                    treeLoader.baseParams.load = v;
                    tree.root.reload();
                },
                width: 150
            }),'-',{
                xtype: 'button',
                text: 'new',
                listeners: {
                    click: function(){
                        tree.ontologyCreatorForm();
                    }
                }
            }
        ], // eof tbar

        // binding functionality to events (e.g. click)
        listeners: {
            // when a node in the ontology widget is clicked
            // force the property widget to load the corresponding
            // properties for this node
            click: function(n) {
                propertyTreeLoader.baseParams.load = n.attributes.id;
                propertyTree.root.reload();
                propertyTree.root.setText(n.text);
            },

            // when the ontology widget starts a remote ajax call
            // to load children, a "loading" message is displayed
            // in the tools status bar
            beforeload: function(){
                Ext.getCmp('basic-statusbar').register();
            },

            // after an ajax call the "loading" message in the status bar
            // will be removed
            load: function(){
                Ext.getCmp('basic-statusbar').unregister();
            }
        }, // eof listeners

        // method that returns a form to create
        // a new ontology class
        ontologyCreatorForm: function(){
            // form to create a new ontology class
            var form = new Ext.form.FormPanel({
                title: 'class creator',
                bodyStyle: 'padding:5px 5px 0',
                width: 420,

                // bottom bar of the form
                // containing a button "save" that starts
                // an ajax call to the tools backend to save
                // the created class in DBpedia
                bbar: ['->',{
                    xtype: 'button',
                    text: 'save',

                    // binding functionalty to the save button
                    listeners: {
                        click: function(el, e){
                            // fetch the values from the form fields defined
                            // in the items section below
                            var title      = Ext.getCmp('class_form_title').getValue().replace(/OntologyClass\:/, '');
                            var label      = Ext.getCmp('class_form_label').getValue();
                            var comment    = Ext.getCmp('class_form_comment').getValue();
                            var subclassof = Ext.getCmp('class_form_subclassof').getValue();

                            // creating the ontology class mapping language
                            // representation for DBpedia
                            var text = "{{Class\n";
                            if(label){
                                text += "|rdfs:label@en=" + label + "\n";
                            }
                            if(comment){
                                text += "|rdfs:comment@en=" + comment + "\n";
                            }
                            if(subclassof){
                                text += "|rdfs:subClassOf=" + subclassof + "\n";
                            }
                            text += "}}";

                            // checks if the new class has an exsiting title and label
                            // the title is used as DBpedia title
                            if(label && title && text){
                                // use jQuery to make a synchronous ajax request
                                // to the backend
                                $.ajax({
                                    url: Ext.HTTP_SERVICE_URL + '/api.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    async: false,
                                    data: {
                                        'action': 'ontologyclass_save',
                                        'lang':lang_parameter,
                                        'titles': 'OntologyClass:' + title,
                                        'text': text
                                    },

                                    // when the ajax call was successful present a confirmation box
                                    // so the user can decide to reload the ontology widget containing
                                    // the new ontology class
                                    success: function(json){
                                        Ext.Msg.confirm('Notification', 'Reload the ontology tree?', function(btn, text){
                                            if(btn == 'yes'){
                                                tree.root.reload();
                                            }
                                        });
                                        x.close();
                                    },

                                    // when something went wrong saving the ontology class
                                    // present an error message to the user
                                    error: function(){
                                        Ext.Msg.alert('Info', 'an error occured');
                                    }
                                }); // eof $.ajax()
                            } else {
                                Ext.Msg.alert('Notification', 'title or label missing');
                            }
                        } // eof listeners->click()
                    } // eof listeners
                }], // eof bbar

                // creating form fields
                items: [
                    new Ext.form.TextField({
                        id: 'class_form_title',
                        width: 250,
                        allowBlank: false,
                        fieldLabel: 'title in english*'
                    }),
                    new Ext.form.TextField({
                        id: 'class_form_label',
                        width: 250,
                        allowBlank: false,
                        fieldLabel: 'label in english*'
                    }),
                    new Ext.form.TextField({
                        id: 'class_form_comment',
                        width: 250,
                        fieldLabel: 'comment in english'
                    }),
                    // auto completion form field for subClassOf
                    // to present the user already existing ontology classes
                    new Ext.form.ComboBox({
                        id: 'class_form_subclassof',
                        tpl: '<tpl for="."><div ext:qtip="{label}" class="x-combo-list-item">{name}</div></tpl>',
                        store: new Ext.data.JsonStore({
                            url: Ext.HTTP_SERVICE_URL + '/api.php',
                            baseParams: {
                                 lang: lang_parameter,
                                action: 'ontology_autocomplete'
                            },
                            totalProperty: 'total',
                            fields: [
                                {name: 'name', label: 'label'}
                            ],
                            root: 'data',
                            sortInfo:{field: "name", direction: "ASC"}
                        }) // eof data store
                        ,minChars:1
                        ,fieldLabel: 'subClassOf'
                        ,displayField: 'name'
                        ,loadingText: 'templates loading'
                        ,forceSelection:true
                        ,lazyRender: true
                        //,typeAhead: true
                        ,valueNotFoundText: 'no class found...'
                        ,mode: 'remote'
                        ,triggerAction: 'all'
                        ,emptyText: 'ontology class'
                        ,width: 250
                    }) // eof ComboBox
                ] // eof items
            }); // eof form

            // embed the form in an Ext JS window
            // and then show the window
            var x = new Ext.Window({
                alwaysOnTop: true
            });
            x.add(form);
            x.show();
        } // eof ontologyCreatorForm()
    }); // eof tree (ontology widget)

    // root node of the Wikipedia template properties widget tree
    var wikiTemplateTreeRootNode = new Ext.tree.TreeNode({
        text: 'Wikitemplate Properties',
        draggable: false,
        expanded: true
    });

    // Wikipedia template properties widget
    var wikiTemplateTree = new Ext.tree.TreePanel({
         anchor: '100%, 50%'
        ,id: 'wikiTemplateTree'
        ,autoScroll: true
        ,enableDrag: true
        ,border: true
        ,loader: new Ext.tree.TreeLoader()
        ,root: wikiTemplateTreeRootNode
        //,containerScroll: true

        // top tool bar containing a search field,
        // an example button which request a list with
        // Wikipedia articles using the current template
        ,tbar: [
            'Search: ',
            ' ',
            new Ext.ux.form.SearchField({
                emptyText: 'wiki property',
                onTrigger1Click: function(){
                    this.custSearch();
                    return false;
                },
                onTrigger2Click: function(){
                    this.custSearch();
                    return false;
                },
                custSearch: function (){
                    var v = this.getRawValue();
                    /* search word too short */
                    if(v.length > 0 && v.length < 3){
                        Ext.Msg.alert('Info', 'your search query is too short (min 3 characters)');
                        return;
                    }
                    /* run filter */
                    wikiTemplateTreeFilter.clear(); // clear existing filter
                    wikiTemplateTreeFilter.filter(new RegExp(Ext.escapeRe(v), "i"), 'text', this.root); // run filter
                },
                width: 150
            }),// eof SearchField
            '-',
            new Ext.Button({text:'test', listeners:{click:function(el,e)
            {
                      window.open("http://mappings.dbpedia.org/server/"+mapping_route+"/extractionSamples/"+mapping_alias+":"+Ext.getCmp('templatename').getValue().replace(new RegExp(template_alias+":", "g"), '').replace(/Template\:/, '') );
            }
              }
            }),
            new Ext.Button({
                text: 'examples',

                // bind functionalty to button event
                listeners: {
                    click: function(el, e){

                        // init an empty menu to fill it
                        // with a list of links to
                        // Wikipedia articles
                        var examplePagesMenu = new Ext.menu.Menu({
                            items: []
                        });
                        
                        // fetch list of Wikipedia articles using a given template
                        // from the tools backend with a jQuery ajax request
                        $.ajax({
                            url: Ext.HTTP_SERVICE_URL + '/api.php',
                            async: false,
                            dataType: 'json',
                            data: {
                                 'lang' : lang_parameter,
                                'titles': 'Template:' + Ext.getCmp('templatename').getValue().replace(new RegExp(template_alias+":", "g"), '').replace(/Template\:/, ''),
                                'action': 'examples'
                            },

                            // on success add each found Wikipedia article
                            // url to the menu (see above)
                            success: function(json){
                                
                                $(json).each(function(){
                                    var site = this;
                                    examplePagesMenu.add(
                                        new Ext.Action({
                                            text: site.name,
                                            url: site.url,
                                            handler: function() {
                                                window.open(this.url);
                                            }
                                        }) // eof action
                                    );
                                });
                            } // eof success
                        }); // eof $.ajax()

                        // if the menu is not empty and the button
                        // examples has been clicked, then show the
                        // menu at mouse position, otherwise alert
                        // that no Wikipedia article uses this template
                        if (examplePagesMenu.items.length > 0) {
                            examplePagesMenu.showAt(e.getXY());
                        } else {
                            Ext.Msg.alert('Notification', 'no wikipedia pages found using the requested template');
                        }
                    }
                }
            }) // eof button examples
        ],

        // bind functionality to the Wikipedia template
        // properties widget
        listeners: {
            // add a "loading" message to the tools
            // status bar, when an ajax request is send
            beforeload: function(){
                Ext.getCmp('basic-statusbar').register();
            },
            load: function(){
                Ext.getCmp('basic-statusbar').unregister();
            }
        }
    }); // eof wikiTemplateTree

    // extend property tree with filter
    var wikiTemplateTreeFilter = new Ext.tree.TreeFilter(wikiTemplateTree);

    // Ext JS tree loader for the ontology property widget
    var propertyTreeLoader = new Ext.tree.TreeLoader({
        dataUrl: Ext.HTTP_SERVICE_URL + '/api.php',
        baseParams: {
            lang: lang_parameter,
            action: 'properties',
            load: 'initial'
        }
    }); // eof propertyTreeLoader

    // init the ontology property widget
    var propertyTree = new Ext.tree.TreePanel({
         title: 'Properties'
        ,rootVisible: true
        ,enableDrag: true
        ,border: true
        ,loader: propertyTreeLoader
        ,anchor: '100%, 50%'
        ,autoScroll: true
        ,root: new Ext.tree.AsyncTreeNode({
            text: 'Properties',
            draggable: false
        })

        // init the top tool bar containing a search field
        // and a button to call a form to create a new property
        ,tbar: [
            'Search: ',
            ' ',
            new Ext.ux.form.SearchField({
                emptyText: 'ontology property',
                onTrigger1Click: function(){
                    this.custSearch();
                    return false;
                },
                onTrigger2Click: function(){
                    this.custSearch();
                    return false;
                },
                custSearch: function (){
                    var v = this.getRawValue();
                    /* search word too short */
                    if(v.length > 0 && v.length < 3){
                        Ext.Msg.alert('Info', 'your search query is too short (min 3 characters)');
                        return;
                    }
                    /* run filter */
                    propertyTreeFilter.clear(); // clear existing filter
                    propertyTreeFilter.filter(new RegExp(Ext.escapeRe(v), "i"), 'label', this.root); // run filter
                },
                width: 150
            }),'-',{
                xtype: 'button',
                text: 'new',
                listeners: {
                    click: function(){
                        propertyTree.propertyCreatorForm();
                    }
                }
            }
        ], // eof tbar

        listeners: {
            beforeload: function(){
                var statusBar = Ext.getCmp('basic-statusbar');
                statusBar.showBusy();
            },
            load: function(){
                var statusBar = Ext.getCmp('basic-statusbar');
                statusBar.clearStatus({useDefaults:true});
            }
        },

        // method returns a form to create a new ontology property
        propertyCreatorForm: function(){
            var form = new Ext.form.FormPanel({
                title: 'property creator',
                bodyStyle: 'padding:5px 5px 0',
                width: 420,

                // bottom tool bar containing a save button
                // which send an ajax request to the tools
                // back end to save new property
                bbar: ['->',{
                    xtype: 'button',
                    text: 'save',
                    listeners: {
                        click: function(el, e){
                            var title   = Ext.getCmp('property_form_title').getValue().replace(/OntologyProperty\:/, '');
                            var label   = Ext.getCmp('property_form_label').getValue();
                            var comment = Ext.getCmp('property_form_comment').getValue();
                            var domain  = Ext.getCmp('property_form_domain').getValue();
                            var type    = Ext.getCmp('property_form_type').getValue().value;
                            var range   = Ext.getCmp('property_form_range').getValue();

                            var text = "{{";
                            if(type){
                                text += type + "\n";
                            }
                            if(label){
                                text += " |rdfs:label@en=" + label + "\n";
                            }
                            if(comment){
                                text += " |rdfs:comment@en=" + comment + "\n";
                            }
                            if(domain){
                                text += " |rdfs:domain=" + domain + "\n";
                            }
                            if(range){
                                text += " |rdfs:range=" + range + "\n";
                            }
                            text += "}}";

                            if(type && label && title && text){
                                $.ajax({
                                    url: Ext.HTTP_SERVICE_URL + '/api.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    async: false,
                                    data: {
                                          'lang' : lang_parameter,
                                        'action': 'property_save',
                                        'titles': 'OntologyProperty:' + title,
                                        'text': text
                                    },
                                    success: function(json){
                                        Ext.Msg.confirm('Notification', 'Reload the property tree?', function(btn, text){
                                            if(btn == 'yes'){
                                                propertyTree.root.reload();
                                            }
                                        });
                                        x.close();
                                    },
                                    // if a property is already defined under this title
                                    // ask if saving process should be forced anyways
                                    error: function(xhr, textStatus, thrownError){
                                        var response = Ext.util.JSON.decode(xhr.responseText);
                                        Ext.Msg.confirm('Info', "Failed with message: [" + response.message + "]<br>Do you want to replace the current property?<hr>current:<br>" + response.currentMarkup + "<br>" + "<hr>new:" + "<br>" + response.newMarkup, function(btn, text){
                                            if(btn == 'yes'){
                                                // force overriding existing property
                                                $.ajax({
                                                    url: Ext.HTTP_SERVICE_URL + '/api.php',
                                                    type: 'POST',
                                                    dataType: 'json',
                                                    async: false,
                                                    data: {
                                                        'action': 'property_save_force',
                                                        'lang' : lang_parameter,
                                                        'titles': response.newTitle,
                                                        'text': response.newMarkup
                                                    },
                                                    success: function(json){
                                                        Ext.Msg.confirm('Notification', 'Reload the property tree?', function(btn, text){
                                                            if(btn == 'yes'){
                                                                propertyTree.root.reload();
                                                            }
                                                        });
                                                        x.close();
                                                    }
                                                }); // eof $.ajax
                                            }
                                        });
                                    }
                                }); // eof $.ajax
                            }
                        }
                    } // eof listeners
                }], // eof bbar

                // form fields for the form to create
                // a new ontology property
                items: [{
                        xtype: 'radiogroup',
                        fieldLabel: 'type*',
                        id: 'property_form_type',
                        items: [
                            {boxLabel: 'ObjectProperty', value: 'ObjectProperty', name: 'property_form_type_1', checked: true},
                            {boxLabel: 'DatatypeProperty', value: 'DatatypeProperty', name: 'property_form_type_1'}
                        ]
                    },
                    new Ext.form.TextField({
                        id: 'property_form_title',
                        width: 250,
                        allowBlank: false,
                        fieldLabel: 'title in english*'
                    }),
                    new Ext.form.TextField({
                        id: 'property_form_label',
                        width: 250,
                        allowBlank: false,
                        fieldLabel: 'label in english*'
                    }),
                    new Ext.form.TextField({
                        id: 'property_form_comment',
                        width: 250,
                        fieldLabel: 'comment'
                    }),
                    new Ext.form.ComboBox({
                        id: 'property_form_domain',
                        tpl: '<tpl for="."><div ext:qtip="{label}" class="x-combo-list-item">{name}</div></tpl>',
                        store: new Ext.data.JsonStore({
                            url: Ext.HTTP_SERVICE_URL + '/api.php',
                            baseParams: {
                                action: 'ontology_autocomplete'    ,
                                lang : lang_parameter

                            },
                            totalProperty: 'total',
                            fields: [
                                {name:'name', label:'label'}
                            ],
                            root: 'data',
                            sortInfo:{field: "name", direction: "ASC"}
                        })
                        ,minChars: 1
                        ,fieldLabel: 'domain'
                        ,displayField: 'name'
                        ,loadingText: 'templates loading'
                        ,forceSelection: true
                        ,lazyRender: true
                        //,typeAhead: true
                        ,valueNotFoundText: 'no class found...'
                        ,mode: 'remote'
                        ,triggerAction: 'all'
                        ,emptyText: 'ontology class'
                        ,width:  250
                    }),
                    new Ext.form.TextField({
                        id: 'property_form_range',
                        width: 250,
                        fieldLabel: 'range'
                    })
                ] // eof items
            }); // eof form

            // bind form to an Ext JS window
            // and show the window
            var x = new Ext.Window({
                alwaysOnTop: true
            });
            x.add(form);
            x.show();
        } // eof propertyCreatorForm()
    }); // eof propertyTree

    // extend property tree with filter
    var propertyTreeFilter = new Ext.tree.TreeFilter(propertyTree);

    // Ext JS data loader for the template widget
    var templateTreeLoader = new Ext.tree.TreeLoader({
        dataUrl: Ext.HTTP_SERVICE_URL + '/api.php',
        baseParams: {
            lang: lang_parameter,
            action: 'template',
            load: 'initial'
        }
    }); // eof templateTreeLoader

    // init template widget
    var templateTree = new Ext.tree.TreePanel({
         title: 'Templates'
        ,rootVisible: false
        ,enableDrag: true
        ,border: true
        ,anchor: '100%, 50%'
        ,autoScroll: true
        ,loader: templateTreeLoader
        ,root: new Ext.tree.AsyncTreeNode({
            text: 'DBpedia Templates'
        }),
        listeners: {
            beforeload: function(){
                Ext.getCmp('basic-statusbar').register();
            },
            load: function(){
                Ext.getCmp('basic-statusbar').unregister();
            }
        }
    }); // eof templateTree

    // Ext JS data loader for the units widget
    var unitTreeLoader = new Ext.tree.TreeLoader({
        dataUrl: Ext.HTTP_SERVICE_URL + '/api.php',
        baseParams: {            
            lang:lang_parameter,
            action: 'unit',
            load: 'initial'
        }
    }); // eof unitTreeLoader

    // init units widget
    var unitTree = new Ext.tree.TreePanel({
         title: 'Datatypes'
        ,rootVisible: false
        ,enableDrag: true
        ,border: true
        ,autoScroll: true
        ,anchor: '100%, 100%'
        ,loader: unitTreeLoader
        ,root: new Ext.tree.AsyncTreeNode({
          text: 'Datatypes'
        }),

        // top tool bar containing a search field and
        // a button to call a form to create a new unit
        tbar: [
            'Search: ',
            ' ',
            new Ext.ux.form.SearchField({
                emptyText: 'datatype',
                onTrigger1Click: function(){
                    this.custSearch();
                    return false;
                },
                onTrigger2Click: function(){
                    this.custSearch();
                    return false;
                },
                custSearch: function (){
                    var v = this.getRawValue();
                    /* search word too short */
                    if(v.length > 0 && v.length < 3){
                        Ext.Msg.alert('Info', 'your search query is too short (min 3 characters)');
                        return;
                    }
                    /* run filter */
                    unitTreeFilter.clear(); // clear existing filter
                    unitTreeFilter.filter(new RegExp(Ext.escapeRe(v), "i"), 'text', this.root); // run filter
                },
                width: 150
            }),'-',{
                xtype: 'button',
                text: 'new',
                listeners: {
                    click: function(){
                        unitTree.datatypeCreatorForm();
                    }
                }
            }
        ], // eof tbar

        // add "loading" message to the tools status bar
        // when doing an ajax call
        listeners: {
            beforeload: function(){
                Ext.getCmp('basic-statusbar').register();
            },
            load: function(){
                Ext.getCmp('basic-statusbar').unregister();
            }
        },

        // method to show a window containing a form to
        // create a new ontology unit
        datatypeCreatorForm: function(){
            // init the form to create a new ontology unit
            var form = new Ext.form.FormPanel({
                title: 'datatype creator',
                bodyStyle: 'padding:5px 5px 0',
                width: 420,
                bbar: ['->',{
                    xtype: 'button',
                    text: 'save',
                    listeners: {
                        click: function(el, e){
                            var title = Ext.getCmp('datatype_form_title').getValue().replace(/Datatype\:/, '');

                            if( title && title != '' ){
                                $.ajax({
                                    url: Ext.HTTP_SERVICE_URL + '/api.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    async: false,
                                    data: {
                                    'lang': lang_parameter,
                                        'action': 'datatype_save',
                                        'titles': 'Datatype:' + title,
                                        'text': '{{DisclaimerDatatype}}'
                                    },
                                    success: function(json){
                                        Ext.Msg.alert('Info', json.message);
                                    }
                                }); // eof $.ajax()
                            } else {
                                Ext.Msg.alert('Notification', 'no title given');
                            }
                            x.close();
                        }
                    }
                }], // eof bbar

                // form fields
                items: [
                    new Ext.form.TextField({
                        id: 'datatype_form_title',
                        width: 250,
                        allowBlank: false,
                        fieldLabel: 'title'
                    })
                ]
            }); // eof form

            // bin the form to an Ext JS window
            // and show the window
            var x = new Ext.Window({
                alwaysOnTop: true
            });
            x.add(form);
            x.show();

        } // eof datatypeCreatorForm
    }); // eof unitTree

    // extend property tree with filter
    var unitTreeFilter = new Ext.tree.TreeFilter(unitTree);

    // root node of the mapping widget
    var dbpediaMappingRoot = new Ext.tree.AsyncTreeNode({
         text: 'TemplateMapping'
        ,name: 'TemplateMapping'
        ,iconCls: 'my-tree-icon-mapper'
        ,isRoot: true
        ,expanded: true
        ,level: 0
    }); // eof dbpediaMappingRoot

    
    var mappingsTreeLoader = new Ext.dbpedia.TreeLoader({
        dataUrl: Ext.HTTP_SERVICE_URL + '/api.php',
        baseParams: {
            action: 'map',
            lang: lang_parameter
            //load: 'initial'
        },
        listeners: {
            loadException: function(tree, node, response){
                //console.debug(text);
                //console.debug(response);
                Ext.Msg.confirm('Notification', 'Mapping not valid.<br />"' + Ext.util.JSON.decode(response.responseText).message + '"<br /><br />Load raw mapping into the "reverse" tab?', function(btn, text){
                    if(btn == 'yes'){
                        Ext.getCmp('input').setValue(Ext.util.JSON.decode(response.responseText).raw);
                    }
                });
                //Ext.Msg.alert('Error', 'Mapping not valid.<br />"' + Ext.util.JSON.decode(response.responseText).message + '"');
                return false;
            }
        }
    });


    var DBpediaMapperTree = new Ext.tree.TreePanel({
        title: 'Mapper',
        enableDrop: true,
        // override parent dropConfig drop zone
        // to prevent node insert 'above'
        // and node insert 'below'
        dropConfig: {
            isValidDropPoint : function(n, pt, dd, e, data){
                if(!n || !data){ return false; }
                var targetNode = n.node;
                var dropNode = data.node;
                // default drop rules
                if(!(targetNode && targetNode.isTarget && pt)){
                    return false;
                }
                if(pt == "append" && targetNode.allowChildren === false){
                    return false;
                }
                if((pt == "above" || pt == "below") && (targetNode.parentNode && targetNode.parentNode.allowChildren === false)){
                    return false;
                }
                if(dropNode && (targetNode == dropNode || dropNode.contains(targetNode))){
                    return false;
                }
                // reuse the object
                var overEvent = this.dragOverData;
                overEvent.tree = this.tree;
                overEvent.target = targetNode;
                overEvent.data = data;
                overEvent.point = pt;
                overEvent.source = dd;
                overEvent.rawEvent = e;
                overEvent.dropNode = dropNode;
                overEvent.cancel = false;
                var result = this.tree.fireEvent("nodedragover", overEvent);
                // @FIX always disable "above" and "below"
                if(pt == "above" || pt == "below"){
                    return false;
                }
                return overEvent.cancel === false && result !== false;
            }
        },
        //autoHeight: true,
        //autoWidth: true,
        anchor: '100%, 100%',
        autoScroll: true,
        allowLeafAppend: true,
        loader: mappingsTreeLoader,
        rootVisible: false,
        border: false,
        tbar: [{
                xtype: 'button',
                text: 'expand tree',
                listeners: {
                    click: function(e){
                        //Ext.Msg.alert('x', 'test');
                        //console.debug(DBpediaMapperTree.getRootNode());
                        //DBpediaMapperTree.getRootNode().expandChildNodes(true);
                        DBpediaMapperTree.expandAll();
                    }
                }
            },{
                xtype: 'button',
                text: 'collapse tree',
                listeners: {
                    click: function(e){
                        //Ext.Msg.alert('x', 'test');
                        //console.debug(DBpediaMapperTree.getRootNode());
                        DBpediaMapperTree.getRootNode().collapseChildNodes(true);
                        //DBpediaMapperTree.expandAll();
                    }
                }
            },{
                xtype: 'button',
                text: 'reload',
                listeners: {
                    click: function(e){
                        var templateName = Ext.getCmp('templatename').getValue();
                        if(templateName == undefined || templateName == ''){
                            Ext.Msg.alert('no template defined to load');
                            return false;
                        }
                        loadTemplates(Ext.getCmp('templatename').getValue());
                    }
                    /*
                    click: function(e){
                        mappingsTreeLoader.baseParams.load = 'initial';
                        DBpediaMapperTree.root.reload();
                        DBpediaMapperTree.expandAll();
                        mappingsTreeLoader.baseParams.load = '';
                    }
                    */
                }
            },{
                xtype: 'button',
                text: 'validate tree',
                listeners: {
                    click: function(e){
                        DBpediaMapperTree.expandAll();
                        DBpediaMapperTree.getRootNode().firstChild.firstChild.validateNode();
                    }
                }
            },{
                xtype: 'button',
                text: 'compare',
                listeners: {
                    click: function(e){
                        checkForMappedWikipediaProperties();
                    }
                }
            }
        ],
        // defining context menu on nodes
        listeners: {
            contextmenu: function(node, e){
                node.select();
                var curNode = node;
                var contextMenu;

                contextMenu = new Ext.menu.Menu({
                    items: [
                        new Ext.Action({
                            text: 'Delete Node [Ctrl+Click]',
                            handler: function() {
                                // remove node from tree
                                if(node.attributes.deletable == false){
                                    Ext.Msg.alert('Info', 'The selected node can not be deleted...');
                                    return;
                                }
                                Ext.Msg.confirm('Question', 'Delete node?', function(btn, text){
                                    if(btn == 'yes'){
                                        node.remove();
                                    }
                                });
                                //this.validateTree();
                            }
                        }),
                        new Ext.Action({
                            text: 'Validate subtree [Alt+Click]',
                            handler: function() {
                                node.cascade(function(e){
                                    e.validateNode();
                                });
                                Ext.Msg.alert('Action executed', 'validate Subtree...');
                            }
                        }),
                        new Ext.Action({
                            text: 'Duplicate Node [Shift+Click]',
                            handler: function() {
                                var clone = node.clone();
                                //clone.expandChildNodes(true);
                                clone.cascade(function(e){
                                    e.attributes.value = '';
                                    e.setText(e.attributes.label + ':');
                                });
                                node.parentNode.appendChild(clone);
                            }
                        }),
                        new Ext.Action({
                            text: 'Edit node',
                            handler: function() {
                              if(curNode.hasChildNodes()){
                                  Ext.Msg.alert('Info', 'You can not edit nodes with child nodes.');
                                  return false;
                              }
                              if(curNode.attributes.isRoot){
                                  Ext.Msg.alert('Info', 'You can not edit the root node.');
                                  return false;
                              }

                              //console.debug(node);
                              var form = new Ext.form.FormPanel({
                                  title: 'Node value editor',
                                  bodyStyle: 'padding:5px 5px 0',
                                  width: 420,
                                  bbar: ['->',{
                                      xtype: 'button',
                                      text: 'save',
                                      listeners: {
                                          click: function(el, e){
                                              var text = Ext.getCmp('node_form_value').getValue();
                                              curNode.attributes.value = text;
                                              curNode.setText(curNode.attributes.label + ': ' + text);

                                              // close form window
                                              x.close();
                                          }
                                      }
                                  }],
                                  items: [
                                      new Ext.form.TextField({
                                          id: 'node_form_text',
                                          width: 250,
                                          fieldLabel: 'label',
                                          disabled: true,
                                          value: node.attributes.label
                                      }),
                                      new Ext.form.TextField({
                                          id: 'node_form_value',
                                          width: 250,
                                          fieldLabel: 'value',
                                          enableKeyEvents: true,
                                          value: node.attributes.value,
                                          listeners: {
                                              keyup: function(elem, evnt){
                                                  if(evnt.getKey() == evnt.ENTER){
                                                      var text = Ext.getCmp('node_form_value').getValue();
                                                      curNode.attributes.value = text;
                                                      curNode.setText(curNode.attributes.label + ': ' + text);
                                                      // close form window
                                                      x.close();
                                                  }
                                              },
                                              scope: this
                                          }
                                      })
                                  ]
                              });

                              var x = new Ext.Window({
                                  alwaysOnTop: true
                              });
                              x.add(form);
                              x.show();
                              //Ext.Msg.alert('Action executed', 'edit node...');
                            }
                        })
                    ]
                });

                // add option to add a specific property
                // via the mouses context menu to a node
                // if the node is a template
                if(node.attributes.isTemplate){
                    var submenu = new Array(); // submenu with available properties
                    
                    // collect a list of possible properties defined by the grammar
                    // by the name of the template element
                    $.each(properties[node.attributes.name], function(n){
                        var n = n;
                        // add each possible property to the submenu array
                        submenu.push(new Ext.Action({
                            text: n,
                            handler: function() {
                                // if this particular property is clicked
                                // iterate on the template widget, select the
                                // matching template element and check for
                                // the chosen property - if found clone the
                                // property node and add it to the selected node
                                var availableTemplates = templateTree.root.childNodes;
                                $.each(availableTemplates, function(index, val){
                                    if(node.attributes.name === val.attributes.name){
                                        val.expand(true);
                                        $.each(val.childNodes, function(index2, val2){
                                            if(n === val2.attributes.label){
                                                var newChildNode = val2.clone();
                                                val2.attributes.deletable = true;
                                                node.appendChild(val2.clone());
                                            }
                                        });
                                    }
                                });
                            }
                        }));
                    });
                
                    // add property sub menu to the main context menu
                    contextMenu.add(new Ext.Action({
                        text: 'Add Property',
                        menu: submenu,
                        handler: function() {
                            Ext.Msg.alert('Action executed', 'property add...');
                        }
                    }));
                }
                
                if (contextMenu) {
                    contextMenu.contextNode = node;
                    contextMenu.showAt(e.getXY());
                }

            },
            nodedrop: function(e){
                this.validateTree();
            },

            beforenodedrop: function(e) {
                if( e.target.attributes.type == 'OntologyClass' ||
                    e.target.attributes.type == 'OntologyProperty' ||
                    e.target.attributes.type == 'TemplateProperty' ||
                    e.target.attributes.type == 'Datatype'
                ){
                    e.target.attributes.value = e.dropNode.attributes.name;
                    e.target.setText(e.target.attributes.label + ': ' + e.dropNode.attributes.label);
                } else {
                    /*
                    if(e.rawEvent.ctrlKey) {  // or whatever criteria you want to make for doing a copy instead of a move
                        e.dropNode = e.dropNode.clone();
                    }
                    */
                    e.dropNode = e.dropNode.clone();
                    e.target.appendChild(e.dropNode);
                    e.target.expandChildNodes(true);
                }
                // @TODO
                //this.validateTree();
                return false;
            },
            nodedragover: function(e){
                // check if type of the drop node
                // matches the expected type of for the target node
                // (allow drop only for valid node types)
                if( e.dropNode.attributes.type != e.target.attributes.type ){
                    return false;
                }

                // if target node and drop node are both templates
                // refuse drop action (attaching templates directly
                // is not possible, you can only attach templates to properties)
                if( e.target.attributes.isTemplate && e.dropNode.attributes.isTemplate ){
                    return false;
                }

                // enable append childs on leafs
                e.target.leaf = false;
                
                // allow drop of node
                return true;
            },
            dblclick: function(n) {
                //Ext.Msg.alert('Info', n.validateDbpediaGrammar());
                //if(n)
                n.expandChildNodes(true);
            }
        },
        root: dbpediaMappingRoot
    });

    // define main window containing the
    // tools widgets
    var win = new Ext.Window({
        width: 1000,
        height: 600,
        closeAction: 'hide',
        //autoDestroy: false,
        //plain: true,
        layout: 'border',
        title: 'DBpedia Ontology Mapper',
        resizeable: true,
        autoScroll: true,
        closable: false,
        //closable: false,
        border: false,
        id: 'window',
        tbar: [{
          xtype: 'button',
          text: 'sync ontology with MediaWiki',
          listeners: {
            click: function(e){
              Ext.Msg.prompt('Password', 'Please enter password:', function(btn, text){
                  if (btn == 'ok'){
                      // Load Ajax response in directly in Ext
                      maskingAjax.request({
                        url: Ext.HTTP_SERVICE_URL + '/api.php',
                        params: {
                            action: 'updateall',
                            lang : lang_parameter,
                            key: Ext.urlEncode({}, text)
                        },
                        success: function(response, opts) {
                            var obj = Ext.decode(response.responseText);
                            Ext.Msg.show({
                                title: 'Info',
                                msg: obj.message,
                                buttons: Ext.Msg.OK,
                                icon: Ext.MessageBox.INFO
                            });
                            Ext.Msg.confirm('Question', 'Reload ontology tree?', function(btn, text){
                                if(btn == 'yes'){
                                    tree.root.reload();
                                }
                            });
                            //console.dir(obj);
                        },
                        failure: function(response, opts) {
                            var obj = Ext.decode(response.responseText);
                            Ext.Msg.show({
                                title: 'Error',
                                msg: obj.message,
                                buttons: Ext.Msg.OK,
                                icon: Ext.MessageBox.ERROR
                            });
                        }
                      });
                  }
              });
            }
          }
        },'-',
        autoCompleteTemplates
        ,{
           xtype: 'button'
          ,text: 'load mapping'
          ,listeners: {
            click: function(e){
              loadTemplates(Ext.getCmp('templatename').getValue());
              //Ext.Msg.alert('Info', Ext.getCmp('templatename').getValue());
            }
          }
        }],
        items: [
            new Ext.Panel({
                title: 'Wikipedia properties',
                width: 300,
                layout: 'anchor',
                region: 'west',
                border: true,
                collapsible: true,
                split: true,
                items: [
                    wikiTemplateTree,
                    templateTree
                ]
            }),
            new Ext.Panel({
                title: 'Mapping Area',
                region: 'center',
                //height: 'auto',
                /*draggable: true,*/
                //autoScroll: true,
                border: true,
                allowLeafAppend: true,
                layout: 'fit',
                items: [
                  new Ext.TabPanel({
                    activeTab: 0,
                    border: true,
                    //split: true,
                    //height: 'auto',
                    autoScroll: true,
                    //width: 'auto',
                    items: [
                      DBpediaMapperTree,
                      new Ext.Panel({
                        title: 'Output',
                        border: false,
                        id: 'outputpanel',
                        autoScroll: true,
                        layout: 'anchor',
                        //anchor: '100%, 100%',
                        //width: 'auto',
                        tbar: [{
                            xtype: 'button',
                            text: 'send to DBpedia',
                            listeners: {
                                click: function(e){
                                    $.ajax({
                                        url: Ext.HTTP_SERVICE_URL + '/api.php',
                                        type: 'POST',
                                        dataType: 'json',
                                        async: false,
                                        data: {
                                            'action': 'mapping_save',
                                            'lang': lang_parameter,
                                            'titles': DBpediaMapperTree.getRootNode().firstChild.attributes.text,
                                            'text': Ext.getCmp('output').getValue()
                                        },
                                        success: function(json){
                                            Ext.Msg.alert('Info', json.message);
                                        },
                                        error: function(xhr, textStatus, thrownError){
                                            var response = Ext.util.JSON.decode(xhr.responseText);
                                            Ext.Msg.alert('Info', response.message + "<br><hr>errors:<br>" + response.errors);
                                        }
                                    });
                                }
                            }
                        },{
                            xtype: 'button',
                            text: 'remote validate',
                            listeners: {
                                click: function(e){
                                    $.ajax({
                                        url: Ext.HTTP_SERVICE_URL + '/api.php',
                                        type: 'POST',
                                        dataType: 'json',
                                        async: false,
                                        data: {
                                             'lang': lang_parameter,
                                            'action': 'remotevalidate',
                                            'titles': DBpediaMapperTree.getRootNode().firstChild.attributes.text,
                                            'text': Ext.getCmp('output').getValue()
                                        },
                                        success: function(json){
                                            Ext.Msg.alert('Info', json.message);
                                        },
                                        error: function(xhr, textStatus, thrownError){
                                            var response = Ext.util.JSON.decode(xhr.responseText);
                                            Ext.Msg.alert('Info', response.message + "<br><hr>errors:<br>" + response.errors);
                                        }
                                    });
                                }
                            }
                        },{
                            xtype: 'button',
                            text: 'regenerate',
                            listeners: {
                                /**
                                 * DBpediaMapping
                                 */
                                click: function(e){
                                    exportDmlToOutputTextbox();
                                }
                            }
                        }],
                        items: [{
                            xtype: 'textarea',
                            id: 'output',
                            anchor: '100%, 100%'
                          }
                        ],
                        listeners: {
                            /**
                             * DBpediaMapping
                             */
                            activate: function(e){
                                exportDmlToOutputTextbox();
                            }
                        }
                      }),
                      new Ext.Panel({
                        title: 'Reverse',
                        border: false,
                        //autoWidth: true,
                        //autoHeight: true,
                        autoScroll: true,
                        layout: 'anchor',
                        tbar: [{
                            xtype: 'button',
                            text: 'apply',
                            listeners: {
                              click: function(e){
                                mappingsTreeLoader.requestMethod = "POST";
                                mappingsTreeLoader.baseParams.load = Ext.getCmp('input').getValue();
                                DBpediaMapperTree.root.reload();
                              }
                            }
                          },{
                            xtype: 'button',
                            text: 'clear',
                            listeners: {
                              click: function(e){
                                Ext.getCmp('input').setValue('');
                              }
                            }
                          }
                        ],
                        listeners: {
                          activate: function(e){
                            this.doLayout();
                          }
                        },
                        items: [{
                            xtype: 'textarea',
                            id: 'input',
                            anchor: '100%, 100%'
                            //width: '100%'
                          }
                        ]
                      })
                    ]
                  })
                ]
            }),
            new Ext.TabPanel({
                title: 'foo',
                width: 300,
                region: 'east',
                activeTab: 0,
                /*draggable: true,*/
                border: true,
                collapsible: true,
                collapseMode: 'mini',
                //hideCollapseTool: true,
                //deferredRender: false,
                split: true,
                items: [
                    new Ext.Panel({
                        title: 'Ontology Browser',
                        //autoScroll: true,
                        border: false,
                        layout: 'anchor',
                        items: [
                            propertyTree,
                            tree
                        ]
                    }),
                    new Ext.Panel({
                        title: 'More',
                        //autoScroll: true,
                        border: false,
                        layout: 'anchor',
                        listeners: {
                            activate: function(e){
                                this.doLayout();
                            }
                        },
                        items: [
                            /*templateTree,*/
                            unitTree
                        ]
                    })
                ]
            })
        ],
        bbar: new Ext.ux.StatusBar({
            id: 'basic-statusbar',
            // defaults to use when the status is cleared:
            defaultText: 'Ready',
            //defaultIconCls: 'default-icon',
            
            // values to set initially:
            text: 'Ready',
            iconCls: 'x-status-valid',
            registeredElements: 0,
            register: function(){
                this.showBusy();
                this.registeredElements++;
            },
            unregister: function(){
                this.registeredElements--;
                if(this.registeredElements < 1){
                    this.clearStatus({useDefaults:true});
                    this.registeredElements = 0;
                }
            }
        }),
        listeners: {
            // always keep windows with alwaysOnTop
            // on top
            activate: function() {
                Ext.WindowMgr.each(function(w) {
                    if (w.alwaysOnTop)
                        w.toFront();
                });
            }
        }
    });

    function exportDmlToOutputTextbox()
    {
        // walk the mapping tree
        DBpediaMapperTree.getRootNode().expandChildNodes(true);
        var text = '';
        DBpediaMapperTree.getRootNode().firstChild.eachChild(function(child){
            text += child.exportToDml(0);
        });
        // @TODO does not work in firefox, but in chrome
        var output =  Ext.getCmp('output');

        text = text.replace(/&gt;/gi,'>');
        text = text.replace(/&lt;/gi, '<');
        output.setRawValue(text);
    }

    function loadTemplates(templateName)
    {
        loadWikipediaTemplate(templateName);
        loadDbpediaTemplates(templateName);
    }

    var loadWikipediaTemplate = function(title){
        App.loadWikipediaTemplate(title, wikiTemplateTreeRootNode, wikiTemplateTree);
    }
    
    function loadDbpediaTemplates(templateName)
    {
        mappingsTreeLoader.requestMethod = "GET";
        var template= templateName.replace(/Template\:/, '');
        var regexp = new RegExp(template_alias+":", "g");
        mappingsTreeLoader.baseParams.titles =mapping_alias+":"  + template.replace(regexp, '');   
        DBpediaMapperTree.root.reload();
        DBpediaMapperTree.expandAll();
    }


    function checkForMappedWikipediaProperties()
    {
        // variable to save a flag for the ontology
        // classes of which the properties are already
        // fetched
        var visitedOntologyClasses = {};

        // temporary variable to store the name of
        // an ontology class in the mapping
        var mappedOntologyClass = "";

        // store of all properties of classes that are
        // used in the mapping
        var mappedWikipediaProperties = new Array();

        // iterate through all nodes of the mapping tree
        DBpediaMapperTree.getRootNode().cascade(function(currentNode){

            // check if one is type of OntologyClass to fetch it's
            // properties
            if( currentNode.attributes.type == 'OntologyClass' ) {

              //fetch the name of the found ontology class
              mappedOntologyClass = currentNode.attributes.value;

              // reload ontology properties of found class, but first check
              // if the found class properties are already fetched

              // @FIX disabled, because property try was not loaded
              // when manually switched ontology class
              //if(!visitedOntologyClasses.mappedOntologyClass){

                  // reload the property tree with the param of the
                  // name of the last found ontology class
                  propertyTreeLoader.baseParams.load = mappedOntologyClass;
                  propertyTree.root.reload();
                  propertyTree.root.setText(mappedOntologyClass);
                  // set the visited flag for this particular ontology class
                  visitedOntologyClasses.mappedOntologyClass = true;
              //}
            }

            // check if the current node is type of template property
            // to store the name of the wikipedia property already mapped
            // in the mapping
            if( currentNode.attributes.type == 'TemplateProperty' ) {
                mappedWikipediaProperties.push(currentNode.attributes.value)
            }
        });

        // array to store all listed Wikipedia Properties
        var availableWikipediaProperties = new Array();

        // iterate through all nodes of the wikipedia tree
        wikiTemplateTreeRootNode.cascade(function(currentNode){

            // add found wikipedia property to list
            availableWikipediaProperties.push(currentNode.text);

            // if current node of available wikipedia properties
            // is in the list of the mapped wikipedia properties
            // then remove the (maybe set) css class that states the
            // property as "not used" and change it to the "in use"
            // css class
            // set also the information bubble (qtip) on mouse over
            if(mappedWikipediaProperties.inArray(currentNode.text)){
                currentNode.getUI().removeClass('unused-props');
                currentNode.getUI().addClass('used-props');
                updateqt(currentNode, "property is mapped", 'Information');
            } else {
                // if the wikipedia property is not yet mapped change the
                // css class of the node to "not used" and remove the
                // (maybe set) css class "in use"
                // set also the information bubble, that the wikipedia
                // property is not yet mapped
                currentNode.getUI().removeClass('used-props');
                currentNode.getUI().addClass('unused-props');
                updateqt(currentNode, "not yet mapped", 'Notification');
            }
        });

        // the next step is to mark all mapped wikipedia template properties,
        // in the mapping tree that are not available / not included in the
        // underlying wikipedia template
        checkForDeprecatedMappedWikipediaProperties(availableWikipediaProperties)
    }

    // function to mark all mapped wikipedia template properties,
    // in the mapping tree that are not available / not included in the
    // underlying wikipedia template
    function checkForDeprecatedMappedWikipediaProperties(availableWikipediaProperties)
    {
        var usedOntologyProperties = new Array();

        DBpediaMapperTree.getRootNode().cascade(function(currentNode){
            if( currentNode.attributes.type == 'TemplateProperty' ) {
                if(availableWikipediaProperties.inArray(currentNode.attributes.value)){
                    currentNode.parentNode.cascade(function(el){
                        el.getUI().addClass('used-mapping-props');
                        //el.markAsSemanticValid('');
                    });
                    // TODO to check if it works
                    currentNode.getUI().iconNode.className = 'x-tree-node-icon my-tree-icon-DBpedia' + currentNode.attributes.type;
                } else {
                    currentNode.parentNode.cascade(function(el){
                        el.getUI().addClass('unused-mapping-props');
                    });
                    // update quick tip
                    updateqt(currentNode, "Template property '" + currentNode.attributes.value + "' unknown", 'Notification');
                    currentNode.getUI().iconNode.className = 'x-tree-node-icon my-tree-icon-DBpedia' + currentNode.attributes.type + '-error';
                }
            }

            if( currentNode.attributes.type == 'OntologyProperty' ) {
                usedOntologyProperties.push(currentNode.attributes.value);
                //console.log(currentNode.attributes.value);
            }

        });

        // mark all mapped ontology properties that are not served by the
        // the ontology referring to this particular used ontology class
        checkForDeprecatedMappedOntologyProperties(usedOntologyProperties);
    }

    // function to mark all mapped ontology properties that are not served by the
    // the ontology referring to this particular used ontology class
    function checkForDeprecatedMappedOntologyProperties(usedOntologyProperties)
    {
        // TODO
        var existingOntologyProps = new Array();
        
        DBpediaMapperTree.getRootNode().cascade(function(currentNode){
            if(currentNode.attributes.type == 'OntologyClass'){
                // make an synchronous ajax call to fetch the properties
                $.ajax({
                    url: Ext.HTTP_SERVICE_URL + '/api.php',
                    async: false,
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        'lang': lang_parameter,
                        'action' : 'properties',
                        'load': currentNode.attributes.value
                    },
                    success: function(json){
                      if ( json ) {
                        for (key in json){
                          existingOntologyProps.push(json[key].name);
                        }
                        return;
                      }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown){
                        Ext.Msg.alert('Notification', 'error while loading ontology properties');
                    }
                });
            }
        });
        
        //console.log(existingOntologyProps);
        
        DBpediaMapperTree.getRootNode().cascade(function(currentNode){
            if(currentNode.attributes.type == 'OntologyProperty'){
                //console.log(currentNode.attributes.value);
                if(!existingOntologyProps.inArray(currentNode.attributes.value)){
                    currentNode.parentNode.cascade(function(el){
                        el.getUI().addClass('unused-mapping-props');
                    });
                    currentNode.getUI().iconNode.className = 'x-tree-node-icon my-tree-icon-DBpedia' + currentNode.attributes.type + '-error';
                    updateqt(currentNode, "Ontology property '" + currentNode.attributes.value + "' not defined for ontology class", 'Notification');
                } else {
                    currentNode.parentNode.cascade(function(el){
                        el.getUI().removeClass('unused-mapping-props');
                    });
                    currentNode.getUI().iconNode.className = 'x-tree-node-icon my-tree-icon-DBpedia' + currentNode.attributes.type;
                }
            }
        });
        
        //console.log(usedOntologyProperties);
        propertyTree.getRootNode().cascade(function(currentNode){
            if(currentNode.attributes.type == 'OntologyProperty'){
                if(usedOntologyProperties.inArray(currentNode.attributes.name)){
                    currentNode.getUI().addClass('used-props');
                } else {
                    currentNode.getUI().removeClass('used-props');
                }
            }
        });

    }



    loadTemplates(requestedTemplate);
    Ext.getCmp('templatename').setValue(requestedTemplate);

    win.show();
}); // end of Ext.onReady()