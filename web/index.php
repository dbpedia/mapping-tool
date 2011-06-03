<?php
require_once 'include.php';
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?php echo $config->page->title; ?></title>
    <?php foreach($config->cdn->css->url as $cssUrl) : ?>
        <link rel="stylesheet" href="<?php echo $cssUrl; ?>" type="text/css" />
    <?php endforeach; ?>
  </head>
  
  <body>
    <script type="text/javascript">
      var requestedTemplate = "<?php echo Tht_Helper_Parameter::hasGET('titles') ? preg_replace('~[^a-zA-Z :.0-9\-]~', '', Tht_Helper_Parameter::GET('titles')) : "Infobox company"; ?>";
    </script>
    
    <?php foreach($config->cdn->js->url as $jsUrl) : ?>
        <script type="text/javascript" src="<?php echo $jsUrl; ?>"></script>
    <?php endforeach; ?>
    
  </body>
</html>