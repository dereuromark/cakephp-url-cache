<?php
/*
 * App Helper url caching
 * Copyright (c) 2009 Matt Curry
 * www.PseudoCoder.com
 * http://github.com/mcurry/cakephp/tree/master/snippets/app_helper_url
 * http://www.pseudocoder.com/archives/2009/02/27/how-to-save-half-a-second-on-every-cakephp-requestand-maintain-reverse-routing
 *
 * @author		Matt Curry <matt@pseudocoder.com>
 * @author		José Lorenzo Rodríguez
 * @license		MIT
 *
 */

App::uses('Helper', 'View');
App::uses('Inflector', 'Utility');

class UrlCacheAppHelper extends Helper {
  var $_key = '';
  var $_extras = array();
  var $_paramFields = array('controller', 'plugin', 'action', 'prefix');

/**
 * This function is responsible for setting up the Url cache before the application starts generating urls in views
 *
 * @return void
 */
  function beforeRender($viewFile) {
  	if (empty($this->request)) {
  		return;
  	}
	$done = Configure::read('UrlCache.runtime.beforeRender');
	if (!$done) {
		if (Configure::read('UrlCache.pageFiles')) {
		  $path = $this->_View->here;
		  if ($this->here == '/') {
			$path = 'home';
		  }
		  $this->_key = '_' . strtolower(Inflector::slug($path));
		}
		$this->_key = 'url_map' . $this->_key;
		UrlCacheManager::$cache = Cache::read($this->_key, '_cake_core_');
		$this->_extras = array_intersect_key($this->request->params, array_combine($this->_paramFields, $this->_paramFields));
		Configure::write('UrlCache.runtime.beforeRender', true);
	}
  }

/**
 * This method will store the current generated urls into a persistent cache for next use
 *
 * @return void
 */
  function afterLayout($layoutFile) {
	$done = Configure::read('UrlCache.runtime.afterLayout');
	if (!$done) {
		Cache::write($this->_key, UrlCacheManager::$cache, '_cake_core_');
		Configure::write('UrlCache.runtime.afterLayout', true);
	}
  }

/**
 * Intercepts the parent url function to first look if the cache was already generated for the same params
 *
 * @param mixed $url url to generate using cakephp array syntax
 * @param boolean $full wheter to generate a full url or not (http scheme)
 * @return string
 * @see Helper::url()
 */
  function url($url = null, $full = false) {
	$keyUrl = $url;
	if (is_array($keyUrl)) {
	  $keyUrl += $this->_extras;
	}

	$key = md5(serialize($keyUrl) . $full);
	if (UrlCacheManager::get($key)) {
	  return UrlCacheManager::get($key);
	}

	$url = parent::url($url, $full);
	UrlCacheManager::set($key, $url);
	return $url;
  }
}

/**
 * This class will statically hold in memory url's indexed by a custom hash
 *
 */
class UrlCacheManager {
/**
 * Holds all generated urls so far by the application indexed by a custom hash
 *
 */
	public static $cache = array();

/**
 * Returns the stored url if it was already generated, false otherwise
 *
 * @param string $key
 * @return mixed
 */
	public static function get($key) {
		return isset(self::$cache[$key]) ? self::$cache[$key] : false;
	}

/**
 * Stores a ney key in memory cache
 *
 * @param string $key
 * @param mixed data to be stored
 * @return void
 */
	public static function set($key, $data) {
		self::$cache[$key] = $data;
	}
}
?>