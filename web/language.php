<div style="text-align:center;">
<h1>DBpedia MappingTool</h1>
<?php
   require_once 'include.php';
 $dirs = array_filter(glob('../config/i18n/*'), 'is_dir');
echo '<p>Please select a language to continue:</p>';
foreach($dirs as $dir){

   //echo end(explode('/',$dir));
   
   $conf = new Zend_Config_Ini($dir."/lang.ini", ENVIRONMENT,true);
   echo "<p><a href='?lang=".$conf->tool->lang->prefix."'>".$conf->tool->lang->name."</a></p>" ;

}




?>
</div>
