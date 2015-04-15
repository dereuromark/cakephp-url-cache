<?php

/**
 * This class will statically hold in memory url's indexed by a custom hash
 *
 * @licence MIT
 * @modified Mark Scherer
 * - now easier to integrate
 * - optimization for `pageFiles` (still stores urls with only controller/action keys in global file)
 * - can handle legacy `prefix` urls
 */
class UrlCacheManager {

	/**
	 * Holds all generated urls so far by the application indexed by a custom hash
	 *
	 */
	public static $cache = [];

	/**
	 * Holds all generated urls so far by the application indexed by a custom hash
	 *
	 */
	public static $cachePage = [];

	/**
	 * Holds all generated urls so far by the application indexed by a custom hash
	 *
	 */
	public static $extras = [];

	/**
	 * type for the current set (triggered by last get)
	 */
	public static $type = 'cache';

	/**
	 * key for current get/set
	 */
	public static $key = null;

	/**
	 * cache key for pageFiles
	 */
	public static $cacheKey = 'url_map';

	/**
	 * cache key for pageFiles
	 */
	public static $cachePageKey = null;

	/**
	 * params that will always be present and will determine the global cache if pageFiles is used
	 */
	public static $paramFields = ['controller', 'plugin', 'action', 'prefix'];

	/**
	 * @var bool
	 */
	public static $modified = false;

	/**
	 * should be called in beforeRender()
	 *
	 * @return void
	 */
	public static function init(View $View) {
		$params = $View->request->params;
		if (Configure::read('UrlCache.pageFiles')) {
			$cachePageKey = '_misc';
			if (is_object($View)) {
				$path = $View->request->here;
				if ($path === '/') {
					$path = 'uc_homepage';
				} else {
					$path = strtolower(Inflector::slug($path));
				}
				if (empty($path)) {
					$path = 'uc_error';
				}
				$cachePageKey = '_' . $path;
			}
			self::$cachePageKey = self::$cacheKey . $cachePageKey;
			if ($cache = Cache::read(self::$cachePageKey, '_cake_core_')) {
				self::$cachePage = $cache;
			}
			if (Configure::read('debug')) {
				Configure::write('UrlCacheDebug.countPage', count(self::$cachePage));
			}
		}
		if ($cache = Cache::read(self::$cacheKey, '_cake_core_')) {
			self::$cache = $cache;
		}
		if (Configure::read('debug')) {
			Configure::write('UrlCacheDebug.count', count(self::$cache));
		}

		# still old "prefix true/false" syntax?
		if (Configure::read('UrlCache.verbosePrefixes')) {
			unset(self::$paramFields[3]);
			self::$paramFields = array_merge(self::$paramFields, (array)Configure::read('Routing.prefixes'));
		}
		self::$extras = array_intersect_key($params, array_combine(self::$paramFields, self::$paramFields));
		$defaults = [];
		foreach (self::$paramFields as $field) {
			$defaults[$field] = '';
		}
		self::$extras = array_merge($defaults, self::$extras);
	}

	/**
	 * should be called in afterLayout()
	 *
	 * @return void
	 */
	public static function finalize() {
		if (Configure::read('debug')) {
			Configure::write('UrlCacheDebug.cache', self::$cache);
			Configure::write('UrlCacheDebug.cachePage', self::$cachePage);
		}

		if (!self::$modified || empty(self::$cache) && empty(self::$cachePage)) {
			return;
		}

		if (Configure::read('debug')) {
			Configure::write('UrlCacheDebug.added', count(self::$cache) - Configure::read('UrlCacheDebug.count'));
			Configure::write('UrlCacheDebug.addedPage', count(self::$cachePage) - Configure::read('UrlCacheDebug.countPage'));
		}

		Cache::write(self::$cacheKey, self::$cache, '_cake_core_');
		if (Configure::read('UrlCache.pageFiles') && !empty(self::$cachePage)) {
			Cache::write(self::$cachePageKey, self::$cachePage, '_cake_core_');
		}
	}

	/**
	 * Returns the stored url if it was already generated, false otherwise
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function get($url, $full) {
		$keyUrl = $url;
		if (is_array($keyUrl)) {
			$keyUrl += self::$extras;
			# prevent different hashs on different orders
			ksort($keyUrl, SORT_STRING);
			# prevent different hashs on different types (int/string/bool)
			foreach ($keyUrl as $key => $val) {
				$keyUrl[$key] = is_array($val) ? Hash::flatten($val) : (String) $val;
			}
		}
		self::$key = md5(serialize($keyUrl) . $full);

		if (Configure::read('UrlCache.pageFiles')) {
			self::$type = 'cachePage';
			if (is_array($keyUrl)) {
				$res = array_diff_key($keyUrl, self::$extras);
				if (empty($res)) {
					self::$type = 'cache';
				}
			}
			if (self::$type === 'cachePage') {
				if (isset(self::$cachePage[self::$key])) {
					if (Configure::read('debug')) {
						Configure::write('UrlCacheDebug.usedPage', (int)Configure::read('UrlCacheDebug.usedPage') + 1);
					}
					return self::$cachePage[self::$key];
				}
				if (Configure::read('debug')) {
					$missed = (array)Configure::read('UrlCacheDebug.missedPage');
					$missed[] = $url;
					Configure::write('UrlCacheDebug.missedPage', $missed);
					Configure::write('UrlCacheDebug.missedCountPage', (int)Configure::read('UrlCacheDebug.missedCountPage') + 1);
				}
				return false;
			}
		}
		if (isset(self::$cache[self::$key])) {
			if (Configure::read('debug')) {
				Configure::write('UrlCacheDebug.used', (int)Configure::read('UrlCacheDebug.used') + 1);
			}
			return self::$cache[self::$key];
		}
		if (Configure::read('debug')) {
			$missed = (array)Configure::read('UrlCacheDebug.missed');
			$missed[] = $url;
			Configure::write('UrlCacheDebug.missed', $missed);
			Configure::write('UrlCacheDebug.missedCount', (int)Configure::read('UrlCacheDebug.missedCount') + 1);
		}
		return false;
	}

	/**
	 * Stores a ney key in memory cache
	 *
	 * @param string $key
	 * @param mixed data to be stored
	 * @return void
	 */
	public static function set($data) {
		self::$modified = true;
		if (Configure::read('UrlCache.pageFiles') && self::$type === 'cachePage') {
			self::$cachePage[self::$key] = $data;
		} else {
			self::$cache[self::$key] = $data;
		}
	}

}
