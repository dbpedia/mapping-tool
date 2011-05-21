/**
 * extending Ext JS
 */
Ext.ns('Ext.dbpedia');

Ext.dbpedia.TreeLoader = Ext.extend(Ext.tree.TreeLoader, {
  // anything what is here can be configured from outside
  initComponent:function() {
      Ext.apply(this, {
        // anything here, e.g. items, tools or buttons arrays,
        // cannot be changed from outside
      }); // e/o apply
      // call parent
      Ext.dbpedia.TreeLoader.superclass.initComponent.apply(this, arguments);
  } // e/o function initComponent
  ,createNode: function(attr){
      // apply baseAttrs, nice idea Corey!
      if(this.baseAttrs){
          Ext.applyIf(attr, this.baseAttrs);
      }
      if(this.applyLoader !== false && !attr.loader){
          attr.loader = this;
      }
      if(Ext.isString(attr.uiProvider)){
         attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
      }

      // @FIX load only nodeTypes that are supported
      if(attr.nodeType && Ext.tree.TreePanel.nodeTypes[attr.nodeType]){
          if(attr.leaf){
              return new Ext.tree.TreePanel.nodeTypes[attr.nodeType](attr);
          } else {

              if(attr.nodeType.search(/Async$/) == -1){
                  return new Ext.tree.TreePanel.nodeTypes[(attr.nodeType + 'Async')](attr);
              }

              return new Ext.tree.TreePanel.nodeTypes[(attr.nodeType)](attr);
          }
      }
      return attr.leaf ?
                  new Ext.tree.TreeNode(attr) :
                  new Ext.tree.AsyncTreeNode(attr);
    }
});

// extended version for custom nodes of
// http://www.extjs.com/forum/showthread.php?4837-how-can-copy-the-Item-but-not-cut-it-on-the-Drag-and-Drop/page2
Ext.override(Ext.tree.TreeNode, {
	clone: function() {
		var atts = this.attributes;
		atts.id = Ext.id(null, "ynode-");
		if(this.childrenRendered || this.loaded || !this.attributes.children) {
            // changed to load custom nodes
			var clone = new Ext.tree.TreePanel.nodeTypes[(atts.nodeType)](Ext.apply({}, atts));
		}
		else {
			var newAtts = Ext.apply({}, atts);
			newAtts.children = this.cloneUnrenderedChildren();
            // changed to load custom nodes
			var clone = new Ext.tree.TreePanel.nodeTypes[(atts.nodeType)](newAtts);
		}

		clone.text = this.text;

		for(var i=0; i<this.childNodes.length; i++){
			clone.appendChild(this.childNodes[i].clone());
		}

		return clone;
	},

	cloneUnrenderedChildren: function() {

		unrenderedClone = function(n) {
			n.id = undefined;
			if(n.children) {
				for(var j=0; j<n.children.length; j++) {
					n.children[j] = unrenderedClone(n.children[j]);
				}
			}
			return n;
		};

		var c = [];
		for(var i=0; i<this.attributes.children.length; i++) {
			c[i] = Ext.apply({}, this.attributes.children[i]);
			c[i] = unrenderedClone(c[i]);
		}

		return c;
	}

});

/**
 * @FIX for Ext.tree.TreeFilter.clear()
 * Ext.tree.TreeFilter.clear() fires exception after
 * realoading an Ext.tree.TreePanel object
 * see forum post
 * http://www.extjs.com/forum/showthread.php?88400-OPEN-497-3.1-Tree-with-treeFilter-errors-on-reload()
 */
Ext.override(Ext.tree.TreeFilter, {
    clear : function(){
        var t = this.tree;
        var af = this.filtered;
        for(var id in af){
            if(typeof id != "function"){
                var n = af[id];
                if(n && n.ui){
                    n.ui.show();
                }
            }
        }
        this.filtered = {};
    }
});