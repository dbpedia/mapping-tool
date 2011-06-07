<div style="text-align:center;">
<h1>DBpedia MappingTool</h1>
<?php
   require_once 'include.php';
 $dirs = array_filter(glob('../config/i18n/*'), 'is_dir');
echo '<p>Please select a language to continue:</p>';
$availableLanguages = $wr->getLanguageNamespaces();
foreach($availableLanguages as $avLang){

   //echo end(explode('/',$dir));
   

   echo "<p><a href='?lang=".$avLang["name"]."'>".$avLang["friendlyName"]."</a></p>" ;

}




?>
</div>
