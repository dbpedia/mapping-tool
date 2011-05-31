<?php
require_once 'include.php';
echo '<?xml version="1.0" encoding="utf-8"?>';


?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
  <meta http-equiv = "Content-Type" content = "text/html;  charset=utf-8">
    <title><?php echo $config->page->title; ?></title>
    <?php foreach($config->cdn->css->url as $cssUrl) : ?>
        <link rel="stylesheet" href="<?php echo $cssUrl; ?>" type="text/css" />
    <?php endforeach; ?>
  </head>
  
  <body>
  <?php
function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}


?>

          
    <script type="text/javascript">
      var requestedTemplate = "<?php echo Tht_Helper_Parameter::hasGET('titles') ? Tht_Helper_Parameter::GET('titles') : $rand_page; ?>";
      var mapping_alias  = "<?php echo $language['mappingAlias']; ?>"         ;
      var mapping_route  = "<?php echo $language['mappingRoute']; ?>"    ;
      var template_alias = "<?php echo $language['wikipediaTemplateAlias']; ?>"    ;
      
      var redirect_alias = "<?php echo 'REDIRECT'; ?>"    ;
      var lang_parameter = "<?php echo $_GET['lang'];?>"   ;
    </script>
    
    <?php
   
      if(isset($_GET["lang"])&& $language!=null)
    foreach($config->cdn->js->url as $jsUrl) {
        echo '<script type="text/javascript" src="'.$jsUrl.'"></script>';
        }
        else{
         include 'language.php';

        }
     ?>
  </body>
</html>