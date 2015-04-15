<?php

App::uses('Helper', 'View');
App::uses('Inflector', 'Utility');
App::uses('UrlCacheManager', 'UrlCache.Routing');

/**
 * App Helper URL caching
 * Copyright (c) 2009 Matt Curry
 * www.PseudoCoder.com
 * http://github.com/mcurry/cakephp/tree/master/snippets/app_helper_url
 * http://www.pseudocoder.com/archives/2009/02/27/how-to-save-half-a-second-on-every-cakephp-requestand-maintain-reverse-routing
 *
 * @author		Matt Curry <matt@pseudocoder.com>
 * @author		José Lorenzo Rodríguez
 * @author		Mark Scherer
 * @license		MIT
 */
class UrlCacheAppHelper extends Helper {

	/**
	 * This function is responsible for setting up the URL cache before the application starts generating urls in views.
	 *
	 * @return void
	 */
	public function beforeRender($viewFile) {
		if (!Configure::read('UrlCache.active') || Configure::read('UrlCache.runtime.beforeRender')) {
			return;
		}
		if (empty($this->request)) {
			return;
		}

		# todo: maybe lazy load with HtmlHelper::url()?
		UrlCacheManager::init($this->_View);
		Configure::write('UrlCache.runtime.beforeRender', true);
	}

	/**
	 * This method will store the current generated URLs into a persistent cache for next use.
	 *
	 * @return void
	 */
	public function afterLayout($layoutFile) {
		if (!Configure::read('UrlCache.active') || Configure::read('UrlCache.runtime.afterLayout')) {
			return;
		}
		if (empty($this->request)) {
			return;
		}

		UrlCacheManager::finalize();
		Configure::write('UrlCache.runtime.afterLayout', true);
	}

	/**
	 * Intercepts the parent URL function to first look if the cache was already generated for the same params
	 *
	 * @param mixed $url URL to generate using CakePHP array syntax
	 * @param boolean $full whether to generate a full url or not (http scheme)
	 * @return string
	 * @see Helper::url()
	 */
	public function url($url = null, $full = false) {
		if (Configure::read('UrlCache.runtime.afterLayout')) {
			return parent::url($url, $full);
		}

		if (Configure::read('UrlCache.active')) {
			if ($cachedUrl = UrlCacheManager::get($url, $full)) {
				return $cachedUrl;
			}
		}
		$routerUrl = parent::url($url, $full);
		if (Configure::read('UrlCache.active')) {
			UrlCacheManager::set($routerUrl);
		}
		return $routerUrl;
	}

}
