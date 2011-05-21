<?php

/**
 * creates all mapping tree widget JavaScript nodes
 * which represent mapping elements and which are defined
 * in the DBpedia Mapping Language Specification
 * by the grammar file
 *
 * the JavaScript output is included in the front end
 */

class Tht_Dml_ExtJavaScriptClasses
{
    public static function writeJs($grammarFile = '')
    {
        // load grammar from xml file
        Tht_Dml_Grammar::loadGrammarFile($grammarFile);

        // add comments to the grammar, because they are not real
        // grammar elements
        $types = array_merge(Tht_Dml_Grammar::getTypeList(), array('Comment'));
?>

var properties = {
<?php $templateList = Tht_Dml_Grammar::getTemplateList(); ?>
<?php for($i=0; $i < count($templateList); $i++): ?>
    <?php $template = Tht_Dml_Grammar::getTemplateByName($templateList[$i]); ?>
    <?php echo $template->name; ?>: {

        <?php $propertyList = $template->getPropertyList(); ?>
        <?php for($j=0; $j < count($propertyList); $j++): ?>
          <?php $property = $template->getPropertyByName($propertyList[$j]); ?>
          <?php echo $property->getName(); ?>: {
            type: "<?php echo $property->type; ?>",
            name: "<?php echo $property->name; ?>",
            isTemplate: <?php echo (Tht_Dml_Grammar::isSupportedTemplate($property->type)) ? 'true' : 'false' ?>,
            min: <?php echo $property->multiplicity['min']; ?>,
            max: <?php echo is_null($property->multiplicity['max']) ? 'undefined' : $property->multiplicity['max']; ?>,
            counter: 0
          }
          <?php echo ($j == count($propertyList)-1) ? '' : ','; ?>
        <?php endfor; ?>
     }
     <?php echo ($i == count($templateList)-1) ? '' : ','; ?>
<?php endfor; ?>
};

<?php foreach ($types as $type) : ?>

            <?php foreach(array('','Async') as $async) : ?>
<?php echo PREFIX . $type . $async; ?> = Ext.extend(Ext.tree.<?php echo $async; ?>TreeNode, {
  syntaxValid: true,
  semanticValid: true,

  // anything what is here can be configured from outside
  constructor: function(config){
      config = config || {};

      Ext.apply(config, {
            iconCls: 'my-tree-icon-<?php echo PREFIX . $type ?>',
            id: Ext.id(this, 'ynode-')
      });
      <?php echo PREFIX . $type . $async; ?>.superclass.constructor.call(this, config);

      this.on('click', function(node, e){
          // @fix for the template tree
          // do not allow methods bind to click for the template tree
          if(this.getOwnerTree().root.attributes.text == 'DBpedia Templates'){
              return false;
          }

          // if node is clicked with pressed SHIFT key and node is
          // type of property mapping, clone the node
          if(e.browserEvent.shiftKey && node.attributes.type == "PropertyMapping"){
              var clone = this.clone();
              clone.cascade(function(e){
                  e.attributes.value = '';
                  e.setText(e.attributes.label + ':');
              });
              this.parentNode.appendChild(clone);
          }

          // if node is clicked with pressed CTRL key, remove node
          if(e.browserEvent.ctrlKey && node.attributes.deletable != false){
              Ext.Msg.confirm('Question', 'Delete node?', function(btn, text){
                  if(btn == 'yes'){
                      node.remove();
                  }
              });
          }

          // if node is clicked with pressed ALT key, validate the current
          // node and its sub nodes
          if(e.browserEvent.altKey){
              this.expandChildNodes(true);
              this.validateNode();
          }
      })
  }
  ,isValid: function(){
      if(this.semanticValid && this.syntaxValid){
          return true;
      }
      return false;
  }
  ,setQtipText: function(text){
      updateqt(this, text, 'Notification');
  }
  ,markAsSyntaxInvalid: function(text){
      this.syntaxValid = false;
      this.setQtipText(text);
  }
  ,markAsSemanticInvalid: function(text){
      //this.semanticValid = false;
      this.setQtipText(text);
      //this.setCss();
      this.getUI().addClass('unused-mapping-props');
  }
  ,markAsSemanticValid: function(){
      this.getUI().removeClass('unused-mapping-props');
  }
  ,resetValidation: function(){
      //this.semanticValid = true;
      this.syntaxValid   = true;
  }
  ,setCss: function(){
      var iconCss = 'x-tree-node-icon ';
      if(this.syntaxValid && this.semanticValid){
          iconCss += ' my-tree-icon-DBpedia' + this.attributes.type;
      } else {
          iconCss += ' my-tree-icon-DBpedia' + this.attributes.type + '-error';
      }
      this.getUI().iconNode.className = iconCss;
      
      if(this.isValid()){
          this.setQtipText('node is ok');
      }
      return true;
  }
  ,validateTemplateNode: function(){
      // reset property counter for this node
      for (var key in properties[this.attributes.label]) {
          if(!properties[this.attributes.label].hasOwnProperty(key)){
              return false;
          }
          property = properties[this.attributes.label][key];
          property.counter = 0;
      }

      // iterate over child nodes and call their validation
      // method to check for errors
      var cs = this.childNodes;
      for(var i=0, len=cs.length; i < len; i++) {
          if(!cs[i].validateNode()){
              this.markAsSyntaxInvalid('invalid child node found');
          }
      }

      // check if all necessary properties of the table
      // exist
      for (var key in properties[this.attributes.label]) {
          if(!properties[this.attributes.label].hasOwnProperty(key)){
              return false;
          }
          property = properties[this.attributes.label][key];
          // if a mandatory property is missing mark node as invalid
          if(property.counter < property.min){
              this.markAsSyntaxInvalid('mandatory property "' + property.name + '" missing');
          }

          // if a property is used more often than allowed mark node as invalid
          if(property.max != undefined && property.counter > property.max){
              this.markAsSyntaxInvalid('property "' + property.name + '" used to often');
          }
      }
      
      this.setCss();
      if( this.isValid() ){
          return true;
      }
      return false;
  }
  ,validatePropertyNode: function(){
      // if node has children redirect validation
      if(this.hasChildNodes()){
          return this.validatePropertyNodeWithChildren();
      }

      // check if property is a valid property for the parent template
      if(!properties[this.parentNode.attributes.label][this.attributes.label]){
          this.markAsSyntaxInvalid("property not allowed");
      }

      // check if node has no value
      if( this.attributes.value == undefined || this.attributes.value == null || this.attributes.value == '' ){
          this.markAsSyntaxInvalid("missing value");
      }

      // check if the type of the property matches the type of the value
      if(properties[this.parentNode.attributes.label][this.attributes.label]['type'] != this.attributes.type){
          this.markAsInvalidSyntax("type of property not matching");
      }

      // increase counter for this property
      properties[this.parentNode.attributes.label][this.attributes.label]['counter'] += 1;

      this.setCss();
      if(this.isValid()){
          return true;
      }
      return false;
  }
  ,validatePropertyNodeWithChildren: function(){
      var cs = this.childNodes;
      for(var i=0, len=cs.length; i < len; i++) {
          if(!cs[i].validateNode()){
              this.markAsSyntaxInvalid("invalid child node found");
          }
      }
      
      // increase counter for this property
      properties[this.parentNode.attributes.label][this.attributes.label]['counter'] += 1;

      this.setCss();
      if(this.isValid()){
          return true;
      }
      return false;
  }
  ,validateNode: function(){
      // comments are always valid
      if(this.attributes.label == "Comment"){
          return true;
      }

      // reset results of former validations
      this.resetValidation();

      // choose validation by type of node
      if(this.attributes.isTemplate){
          return this.validateTemplateNode();
      } else {
          return this.validatePropertyNode();
      }
  }
  ,exportToDml: function(level){
      // initialization of mapping string
      var out = '';

      // spaces per level
      var spaces = '  ';

      // if node is template add a new line break and add opening curly braces
      if(this.attributes.isTemplate){
          out += "\r\n" + spaces.repeat(level) + '{{ ' + this.attributes.label;

      // if node is a comment add a new line and add comment opening tag
      } else if(this.attributes.label.search(/Comment$/) != -1) {
          out += "\r\n" + spaces.repeat(level) + '&lt;!-- ';
    
      // if node is a property add a dash and the name of the property
      } else {
          out += ' | ' + this.attributes.label + ' = ';
      }

      // iterate on child nodes and call exportToDml() recursively
      // ann add the output to the mapping
      this.eachChild(function(el){
           out += el.exportToDml(level+1);
      });

      // if node has no child nodes, than it's a property node
      // and therefor add the value of the node
      if(!this.hasChildNodes()){
          out += this.attributes.value;
      }

      // if node is template add closing opening curly braces
      if(this.attributes.isTemplate){
          out += " }}";

      // if node is a comment add comment closing tags
      } else if(this.attributes.label.search(/Comment$/) != -1) {
          out += ' --&gt;' + "\r\n";
      }

      // return the mapping string
      return out + "";
  }
});
Ext.tree.TreePanel.nodeTypes.<?php echo PREFIX . $type . $async; ?> = <?php echo PREFIX . $type . $async; ?>;

<?php       endforeach; ?>
<?php   endforeach;

    }
}