<h2>URL Cache</h2>

<h3>Global</h3>
URLs already in Cache: <?php echo (int)Configure::read('UrlCacheDebug.count'); ?>
<br>
Hits (reused) this page load: <b><?php echo (int)Configure::read('UrlCacheDebug.used'); ?></b>
<br>
Misses this page load: <b><?php echo (int)Configure::read('UrlCacheDebug.missedCount'); ?></b>
<br>
Newly added this page load: <b><?php echo (int)Configure::read('UrlCacheDebug.added'); ?></b>

<h3>Per Page</h3>
URLs already in Cache for this page: <?php echo (int)Configure::read('UrlCacheDebug.countPage'); ?>
<br>
Hits (reused) this page load: <b><?php echo (int)Configure::read('UrlCacheDebug.usedPage'); ?></b>
<br>
Misses this page load: <b><?php echo (int)Configure::read('UrlCacheDebug.missedCountPage'); ?></b>
<br>
Newly added this page load: <b><?php echo (int)Configure::read('UrlCacheDebug.addedPage'); ?></b>

<h3>URL Details</h3>
Missed:
<pre>
<?php
print_r(Configure::read('UrlCacheDebug.missed'));
?>
</pre>

Global cache:
<pre>
<?php
print_r(Configure::read('UrlCacheDebug.cache'));
?>
</pre>

Specific cache (this page, if enabled):
<pre>
<?php
print_r(Configure::read('UrlCacheDebug.cachePage'));
?>
</pre>
