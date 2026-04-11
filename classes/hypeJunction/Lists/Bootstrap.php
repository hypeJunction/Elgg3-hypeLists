<?php

namespace hypeJunction\Lists;

use Elgg\PluginBootstrap;

class Bootstrap extends PluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function load() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		require_once dirname(dirname(dirname(__DIR__))) . '/lib/functions.php';
	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		$defaults = [
			'page/components/list',
			'page/components/gallery',
			'page/components/ajax_list',
		];

		$views = \elgg_trigger_plugin_hook('get_views', 'framework:lists', null, $defaults);
		foreach ($views as $view) {
			\elgg_register_plugin_hook_handler('view', $view, 'hypelists_wrap_list_view_hook');
			\elgg_register_plugin_hook_handler('view_vars', $view, 'hypelists_filter_vars');
		}

		\elgg_extend_view('elgg.css', 'collection/view.css');
		\elgg_extend_view('elgg.css', 'forms/collection/search.css');

		\elgg_register_plugin_hook_handler('adapter:entity', 'all', [\hypeJunction\Data\Extender::class, 'addData']);
		\elgg_register_plugin_hook_handler('adapter:entity', 'all', [\hypeJunction\Data\Extender::class, 'addPermissions']);
		\elgg_register_plugin_hook_handler('adapter:entity', 'all', [\hypeJunction\Data\Extender::class, 'addCounters']);
		\elgg_register_plugin_hook_handler('adapter:entity', 'all', [\hypeJunction\Data\Extender::class, 'addDataLinks']);

		\elgg_register_plugin_hook_handler('adapter:entity', 'user', [\hypeJunction\Data\Extender::class, 'addUserData']);
		\elgg_register_plugin_hook_handler('adapter:entity', 'group', [\hypeJunction\Data\Extender::class, 'addGroupData']);
		\elgg_register_plugin_hook_handler('adapter:entity', 'object', [\hypeJunction\Data\Extender::class, 'addObjectData']);

		\elgg_register_collection('collection:default', \hypeJunction\Lists\DefaultEntityCollection::class);
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {

	}
}
