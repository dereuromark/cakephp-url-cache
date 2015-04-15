# CakePHP UrlCache plugin
[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-url-cache.png?branch=master)](https://travis-ci.org/dereuromark/cakephp-url-cache)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-url-cache/v/stable.png)](https://packagist.org/packages/dereuromark/cakephp-url-cache)
[![Coverage Status](https://coveralls.io/repos/dereuromark/cakephp-url-cache/badge.png)](https://coveralls.io/r/dereuromark/cakephp-url-cache)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-url-cache/license.png)](https://packagist.org/packages/dereuromark/cakephp-url-cache)
[![Total Downloads](https://poser.pugx.org/dereuromark/cakephp-url-cache/d/total.png)](https://packagist.org/packages/dereuromark/cakephp-url-cache)

For CakePHP 2.x

Whenever you use `$this->Html->link()` in your CakePHP views the CakePHP Router has to scan through all your routes until it finds a match.
This can be slow if you have a lot of links on a page or use a lot of custom routes.  By adding this code to your AppHelper the URLs
are cached, speeding up requests.  The cache settings follow the same rules as the other CakePHP core cache settings.
If debug is set to greater than 0 the cache expires in 10 seconds.  With debug at 0 the cache is good for 999 days.

## Instructions

1. Download the plugin to `app/Plugin/UrlCache` manually or include it via composer.

2. Put at the top of your `app/View/Helper/AppHelper.php`:
   ```php
   App::uses('UrlCacheAppHelper', 'UrlCache.View/Helper');
   ```

3. Load and activate Plugin in `app/Config/bootstrap.php`:

   ```php
   CakePlugin::load('UrlCache');

   Configure::write('UrlCache.active', true);
   ```

4. Have your AppHelper extend UrlCacheAppHelper instead of Helper:

   ```php
   class AppHelper extends UrlCacheAppHelper {
       ...
   }
   ```
5. That's it!  Just continue using `$this->Html->link()` or `$this->Html->url()` as you usually do.

## Configuration
By default all the cache will be stored in one file. This is only recommended for sites with not many links.
If your site has a ton of unique URLs you don't want to store them all in one giant cache which would need to be loaded each request.
So in case you have a large amount of links on specific pages, and if they contain more than just the main
URL fragments (prefix, plugin, controller, action) like with pagination you can set the option `Configure::write('UrlCache.pageFiles', true)`
and each page will additionally keep a separate cache for those unique URLs.
Only the controller/action URLs without named or passed params will then be stored in the global cache.

There is also a `Configure::write('UrlCache.verbosePrefixes', true)` param.
It is useful if you defined some prefixes in your core.php like `Configure::write('Routing.prefixes', ['admin']);`
and if you mainly still use the old 1.2/1.3 syntax for prefixes:
```
'admin' => true/false
```

instead of
```
'prefix' => 'admin'
```

The latter is more future proof, though.

## Debugging
With the de-facto-standard debugging tool [DebugKit](https://github.com/cakephp/debug_kit) you can enable a toolbar panel that allows
you to see how many links have been used and/or added per page view.
To enable it, you can add it to your $components configuration:
```
public $components = ['DebugKit.Toolbar' => ['panels' => ['UrlCache.UrlCache']]];
```

## Benchmark
With a little sample script you can at least approximate the improvement for your (custom) routes:
```php
// In your view ctp:
$timeStart = microtime(true);

$url = ['controller' => 'posts', 'action' => 'view'];
for ($i = 0; $i < 10000; $i++) {
	$u = $url;
	$u[] = $i;
	$link = $this->Html->link($i, $u);
}

$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;
debug($time);
```
With the first request with my test it was 9 seconds, the second hit only 1 second.
So in overall the speed improvement can be **around 10x faster**. It really depends on how dynamic your pages and URLs are, though.
And the above 10000 links is not a realistic scenario, so the overall speed improvement compared to the whole dispatcher process
will be usually a lot less. So you definitely should look into other bottlenecks, usually the DB, opcode caching, and dispatching speed in general.
