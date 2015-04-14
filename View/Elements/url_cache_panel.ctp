<h2>URL Cache</h2>

<h3>Global</h3>
URLs already in Cache: <?php echo Configure::read('UrlCacheDebug.count'); ?>
<br>
Newly added: <b><?php echo (int)Configure::read('UrlCacheDebug.added'); ?></b>

<h3>Per Page</h3>
URLs already in Cache for this page: <?php echo Configure::read('UrlCacheDebug.countPage'); ?>
<br>
Newly added: <b><?php echo (int)Configure::read('UrlCacheDebug.addedPage'); ?></b>
