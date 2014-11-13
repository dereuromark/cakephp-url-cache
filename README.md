# CakePHP UrlCache plugin

For CakePHP 2.x

Whenever you use $this->Html->link() in your CakePHP views the Cake Router has to scan through all your routes until it finds a match.
This can be slow if you have a lot of links on a page or use a lot of custom routes.  By adding this code to your AppHelper the urls
are cached, speeding up requests.  The cache settings follow the same rules as the other Cake core cache settings.
If debug is set to greater than 0 the cache expires in 10 seconds.  With debug at 0 the cache is good for 999 days.

## Instructions

1. Download the plugin to /app/Plugin/UrlCache manually or include it via composer

2. Put at the top of your app/View/Helper/AppHelper.php

   App::uses('UrlCacheAppHelper', 'UrlCache.View/Helper');

3. Load and activate Plugin in app/Config/bootstrap.php

   CakePlugin::load('UrlCache');
 
   Configure::write('UrlCache.active', true);

4. Have your AppHelper extend UrlCacheAppHelper instead of Helper

	class AppHelper extends UrlCacheAppHelper {
		...
	}

5. Thats it!  Just continue using $this->Html->link as you usually do.

By default all the cache will be stored in one file. This is only recommended for sites with not many links.
If your site has a ton of unique urls you don't want to store them all in one giant cache which would need to be loaded each request.
You can set the option Configure::write('UrlCache.pageFiles', true) and each page will additionally keep a seperate cache for those unique urls.
Only the controller/action urls without named or passed params will then be stored in the global cache.

There is also a Configure::write('UrlCache.verbosePrefixes', true) param.
It is useful if you defined some prefixes in your core.php like `Configure::write('Routing.prefixes', array('admin'));`
and if you mainly still use the old 1.2/1.3 syntax for prefixes:

    'admin' => true/false

instead of

    'prefix' => 'admin'

Enjoy!

## Notes

This has been integrated into the Tools plugin. But this can also be used standalone.
