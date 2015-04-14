<h2>URL Cache</h2>
<?php //echo die(debug(Configure::read('UrlCacheDebug.count')));?>
URLs already in Cache for this page: <?php echo Configure::read('UrlCacheDebug.count'); ?>
<br>
Newly added: <b><?php echo (int)Configure::read('UrlCacheDebug.added'); ?></b>
