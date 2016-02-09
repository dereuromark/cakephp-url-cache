<?php

App::uses('DebugPanel', 'DebugKit.Lib');

/**
 * Custom Panel for https://github.com/cakephp/debug_kit
 *
 * Activate it via
 *   'panels' => ['UrlCache.UrlCache']
 *
 */
class UrlCachePanel extends DebugPanel {

	/**
	 * Defines which plugin this panel is from so the element can be located.
	 *
	 * @var string
	 */
	public $plugin = 'UrlCache';

	/**
	 * Not used right now
	 *
	 * @param \Controller|string $controller
	 * @return array
	 */
	public function beforeRender(Controller $controller) {

		return [];
	}

}
