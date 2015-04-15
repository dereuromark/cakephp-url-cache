<?php

App::uses('View', 'View');
App::uses('Controller', 'Controller');
App::uses('Cache', 'Cache');
App::uses('UrlCacheAppHelper', 'UrlCache.View/Helper');
App::uses('CakeRequest', 'Network');

class UrlCacheTest extends CakeTestCase {

	public $HtmlHelper = null;

	public function setUp() {
		parent::setUp();

		Configure::write('UrlCache.active', true);
		Configure::write('UrlCache.pageFiles', true);
		Configure::write('UrlCache.verbosePrefixes', true);
		Configure::write('Routing.prefixes', ['admin']);

		Router::reload();

		$this->HtmlHelper = new FakeHtmlHelper(new View(new Controller(new CakeRequest('/test', false))));
		$this->HtmlHelper->beforeRender('foo');
	}

	public function tearDown() {
		Cache::delete(UrlCacheManager::$cacheKey, '_cake_core_');
		Cache::delete(UrlCacheManager::$cachePageKey, '_cake_core_');
		UrlCacheManager::$cache = [];
		UrlCacheManager::$cachePage = [];
		Configure::delete('UrlCache');

		parent::tearDown();
	}

	public function testInstance() {
		$this->assertTrue(is_a($this->HtmlHelper, 'FakeHtmlHelper'));
	}

	public function testSettings() {
		$settings = Configure::read('UrlCache');
		$this->assertTrue($settings['active']);
	}

	public function testUrlRelative() {
		$url = $this->HtmlHelper->url(['controller' => 'posts']);
		$this->assertEqual($url, '/posts');
		$this->assertEqual(['779e416667ffd33e75ac19e0952abb47' => '/posts'], UrlCacheManager::$cache);

		$this->HtmlHelper->afterLayout('foo');
		$cache = Cache::read(UrlCacheManager::$cacheKey, '_cake_core_');
		$this->assertEqual(['779e416667ffd33e75ac19e0952abb47' => '/posts'], $cache);
	}

	public function testUrlFull() {
		$url = $this->HtmlHelper->url(['controller' => 'posts'], true);
		$this->assertPattern('/http:\/\/(.*)\/posts/', $url);
		$this->assertEqual(['8f45f5c31d138d700742b01ccb673e1e'], array_keys(UrlCacheManager::$cache));
		$this->assertPattern('/http:\/\/(.*)\/posts/', UrlCacheManager::$cache['8f45f5c31d138d700742b01ccb673e1e']);

		$this->HtmlHelper->afterLayout('foo');
		$cache = Cache::read(UrlCacheManager::$cacheKey, '_cake_core_');
		$this->assertEqual(['8f45f5c31d138d700742b01ccb673e1e'], array_keys($cache));
		$this->assertPattern('/http:\/\/(.*)\/posts/', $cache['8f45f5c31d138d700742b01ccb673e1e']);
	}

	public function testUrlRelativeAndFull() {
		$this->HtmlHelper->url(['controller' => 'posts']);
		$this->HtmlHelper->url(['controller' => 'posts'], true);

		$this->assertEqual(['779e416667ffd33e75ac19e0952abb47', '8f45f5c31d138d700742b01ccb673e1e'], array_keys(UrlCacheManager::$cache));

		$this->HtmlHelper->afterLayout('foo');
		$cache = Cache::read(UrlCacheManager::$cacheKey, '_cake_core_');
		$this->assertEqual(['779e416667ffd33e75ac19e0952abb47', '8f45f5c31d138d700742b01ccb673e1e'], array_keys($cache));
	}

	public function testUrlWithParams() {
		$this->HtmlHelper->url(['controller' => 'posts'], true);
		$this->HtmlHelper->url(['controller' => 'posts', 'action' => 'view', '3']);
		$this->HtmlHelper->url(['controller' => 'posts', 'action' => 'index', 'Test' => ['test']]);

		$this->assertEqual(['8f45f5c31d138d700742b01ccb673e1e'], array_keys(UrlCacheManager::$cache));
		$cacheKeys = array_keys(UrlCacheManager::$cachePage);
		$this->assertEqual('e2ff5470228f80f98b2be7ddbcab340d', $cacheKeys[0]);
		$this->assertEqual('8b94c1c80f3dc49fbf8bdaa1bfa4ea09', $cacheKeys[1]);

		$this->HtmlHelper->afterLayout('foo');
		$cache = Cache::read(UrlCacheManager::$cacheKey, '_cake_core_');
		$this->assertEqual(['8f45f5c31d138d700742b01ccb673e1e'], array_keys($cache));
		$cache = Cache::read(UrlCacheManager::$cachePageKey, '_cake_core_');
		$cacheKeys = array_keys($cache);
		$this->assertEqual('e2ff5470228f80f98b2be7ddbcab340d', $cacheKeys[0]);
		$this->assertEqual('8b94c1c80f3dc49fbf8bdaa1bfa4ea09', $cacheKeys[1]);
	}

	public function testUrlWithDifferentOrders() {
		$this->HtmlHelper->url(['plugin' => 'tools', 'controller' => 'posts', 'action' => 'view']);
		$this->HtmlHelper->url(['action' => 'view', 'controller' => 'posts', 'plugin' => 'tools']);
		$this->assertEqual(['0ba1e9d8ab27a564450be9aa93bd4a1c'], array_keys(UrlCacheManager::$cache));

		$this->HtmlHelper->url(['plugin' => 'tools', 'controller' => 'posts', 'action' => 'view', '3']);
		$this->HtmlHelper->url(['action' => 'view', 'controller' => 'posts', 'plugin' => 'tools', '3']);
		$this->assertEqual(['f5509ea5e31562b541948d89b4d65700'], array_keys(UrlCacheManager::$cachePage));

		$this->HtmlHelper->afterLayout('foo');
		$cache = Cache::read(UrlCacheManager::$cacheKey, '_cake_core_');
		$this->assertEqual(['0ba1e9d8ab27a564450be9aa93bd4a1c'], array_keys($cache));
		$cache = Cache::read(UrlCacheManager::$cachePageKey, '_cake_core_');
		$this->assertEqual(['f5509ea5e31562b541948d89b4d65700'], array_keys($cache));
	}

	public function testUrlWithDifferentValues() {
		$url = $this->HtmlHelper->url(['plugin' => false, 'controller' => 'posts', 'action' => 'details', 1]);
		$url2 = $this->HtmlHelper->url(['plugin' => null, 'controller' => 'posts', 'action' => 'details', '1']);
		$url3 = $this->HtmlHelper->url(['plugin' => '', 'controller' => 'posts', 'action' => 'details', true]);
		$this->assertEqual($url, $url2);
		$this->assertEqual($url2, $url3);
		$this->assertEqual(['f0fdde123fe5958781cfad4284ee59c4'], array_keys(UrlCacheManager::$cachePage));

		$this->HtmlHelper->afterLayout('foo');
		$cache = Cache::read(UrlCacheManager::$cachePageKey, '_cake_core_');
		$this->assertEqual(['f0fdde123fe5958781cfad4284ee59c4'], array_keys($cache));
	}

	public function testGlobalCache() {
		$res = UrlCacheManager::get(['controller' => 'posts', 'action' => 'index'], false);
		$this->assertEqual(false, $res);

		$url = $this->HtmlHelper->url(['controller' => 'posts', 'action' => 'index']);
		$this->HtmlHelper->afterLayout('foo');

		# on second page the same url will not trigger a miss but a hit
		$this->HtmlHelper->beforeRender('foo');
		$res = UrlCacheManager::get(['controller' => 'posts', 'action' => 'index'], false);
		$this->assertEqual($url, $res);

		$this->HtmlHelper->afterLayout('foo');
	}

	public function testUrlWithVerbosePrefixes() {
		$url = $this->HtmlHelper->url(['plugin' => 'tools', 'controller' => 'posts', 'action' => 'view', 'admin' => true]);
		$url2 = $this->HtmlHelper->url(['action' => 'view', 'controller' => 'posts', 'plugin' => 'tools', 'admin' => true]);
		$this->assertEqual(['3016e3546edf1c2152905da5a42ea13f'], array_keys(UrlCacheManager::$cache));
		$this->assertEqual('/admin/tools/posts/view', $url);
		$this->assertEqual($url, $url2);

		$this->HtmlHelper->url(['plugin' => 'tools', 'controller' => 'posts', 'action' => 'view', 'admin' => true, 'some' => 'param']);
		$this->HtmlHelper->url(['action' => 'view', 'controller' => 'posts', 'plugin' => 'tools', 'some' => 'param', 'admin' => true]);
		$this->assertEqual(['25a924190d6e0ed09127d1904a286a95'], array_keys(UrlCacheManager::$cachePage));

		$this->HtmlHelper->afterLayout('foo');
	}

	//doesnt work yet
	public function _testUrlWithoutVerbosePrefixes() {
		Configure::delete('UrlCache');
		Configure::write('UrlCache.active', true);
		Configure::write('UrlCache.pageFiles', true);
		Configure::write('UrlCache.verbosePrefixes', false);
		UrlCacheManager::$paramFields = ['controller', 'plugin', 'action', 'prefix'];
		$this->HtmlHelper->beforeRender('foo');

		$url = $this->HtmlHelper->url(['plugin' => 'tools', 'controller' => 'posts', 'action' => 'view', 'prefix' => 'admin']);
		$this->assertEqual(['41d5d7eb9442adbe76e6c7ebbb02ecc7'], array_keys(UrlCacheManager::$cache));
		$this->assertEqual('/admin/tools/posts/view', $url);

		$url = $this->HtmlHelper->url(['plugin' => 'tools', 'controller' => 'posts', 'action' => 'view', 'prefix' => 'admin', 'some' => 'param']);
		$this->HtmlHelper->url(['action' => 'view', 'controller' => 'posts', 'plugin' => 'tools', 'some' => 'param', 'prefix' => 'admin']);
		$this->assertEqual('/admin/tools/posts/view/some:param', $url);
		$this->assertEqual(['6a7091b88c8132ebb5461851808d318a'], array_keys(UrlCacheManager::$cachePage));

		$this->HtmlHelper->afterLayout('foo');
	}

}

/**
 * we dont extend app helper to avoid app specific overrides and errors
 */
class FakeHtmlHelper extends UrlCacheAppHelper {

	public function url($url = null, $full = false) {
		return parent::url($url, $full);
	}

}
