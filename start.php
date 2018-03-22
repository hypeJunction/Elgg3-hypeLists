<?php

/**
 * hypeLists
 * Developer tools for managing and ajaxifying collections
 *
 * @author      Ismayil Khayredinov <ismayil@hypejunction.com>
 * @copyright   Copyright (c) 2014-2018 Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

/**
 * Wrap list views into a container that can be manipulated
 *
 * @param string $hook   "view"
 * @param string $type   "page/components/list" or "page/components/gallery"
 * @param string $view   View
 * @param array  $params Hook params
 *
 * @return string Wrapped view
 */
function hypelists_wrap_list_view_hook($hook, $type, $view, $params) {

	$viewtype = elgg_extract('viewtype', $params, 'default');
	if ($viewtype !== 'default') {
		return;
	}

	$vars = elgg_extract('vars', $params);

	$pagination = elgg_extract('pagination', $vars);
	$pagination_type = elgg_extract('pagination_type', $vars, elgg_get_plugin_setting('pagination_type', 'hypeLists'));

	if ($pagination === false) {
		return;
	}

	if (!$pagination && !$pagination_type) {
		return;
	}

	$no_results = elgg_extract('no_results', $vars, '');
	$no_results_str = ($no_results instanceof Closure) ? $no_results() : $no_results;

	$list_classes = $type == 'page/components/gallery' ? ['elgg-gallery'] : ['elgg-list'];
	if (isset($vars['list_class'])) {
		$list_classes[] = $vars['list_class'];
	}

	$base_url = hypelists_prepare_base_url(elgg_extract('base_url', $vars));

	$list_id = (isset($vars['list_id'])) ? $vars['list_id'] : '';
	if (!$list_id) {
		$list_id = md5(serialize([
			elgg_extract('container_class', $vars),
			implode(' ', $list_classes),
			elgg_extract('item_class', $vars),
			$no_results_str,
			$pagination,
			$base_url,
		]));
	}

	$container_class = array_filter([
		'elgg-list-container',
		elgg_extract('container_class', $vars)
	]);

	$wrapper_params = [
		'class' => implode(' ', $container_class),
		'data-list-id' => $list_id,
		'data-base-url' => $base_url,
		'data-count' => elgg_extract('count', $vars, 0),
		'data-pager' => $pagination ? 'visible' : 'hidden',
		'data-pagination' => $pagination_type,
		'data-pagination-position' => elgg_extract('position', $vars, ($pagination_type === 'infinite') ? 'both' : 'after'),
		'data-pagination-num-pages' => (int) elgg_extract('num_pages', $vars, 5),
		'data-text-no-results' => $no_results_str,
		'data-limit' => elgg_extract('limit', $vars, 10),
		'data-offset' => elgg_extract('offset', $vars, 0),
		'data-offset-key' => elgg_extract('offset_key', $vars, 'offset'),
		'data-lazy-load' => (int) elgg_extract('lazy_load', $vars, 0),
		'data-auto-refresh' => elgg_extract('auto_refresh', $vars, false),
		'data-reversed' => elgg_extract('reversed', $vars, false),
		'data-list-time' => get_input('list_time', time()),
		'data-list-classes' => implode(' ', $list_classes),
	];

	foreach ($vars as $key => $val) {
		if (substr($key, 0, 5) === 'data-' && !array_key_exists($key, $wrapper_params)) {
			$wrapper_params[$key] = $val;
		}
	}

	$script = elgg_view('components/list/require');

	return elgg_format_element('div', $wrapper_params, $view) . $script;
}

/**
 * Filters some of the view vars
 *
 * @param string $hook   "view_vars"
 * @param string $type   List view name
 * @param array  $vars   View vars
 * @param array  $params Hook params
 *
 * @return array
 */
function hypelists_filter_vars($hook, $type, $vars, $params) {

	$vars['base_url'] = hypelists_prepare_base_url(elgg_extract('base_url', $vars));

	return $vars;
}

/**
 * Normalize base_url
 *
 * @param string $base_url Base URL
 *
 * @return string
 */
function hypelists_prepare_base_url($base_url = null) {

	if (empty($base_url)) {
		// navigation/pagination sets this to Referrer on XHR calls
		// that causes trouble
		$base_url = current_page_url();
	}

	// Need absolute URL (embed causes trouble)
	$base_url = elgg_normalize_url($base_url);

	$base_url = elgg_http_remove_url_query_element($base_url, 'limit');
	$base_url = elgg_http_remove_url_query_element($base_url, 'offset');

	return $base_url;
}

/**
 * Register a new collection
 *
 * @param string $name  Collection name
 * @param string $class Collection class
 *
 * @return void
 */
function elgg_register_collection($name, $class) {
	elgg()->collections->register($name, $class);
}

/**
 * Build a new collection
 *
 * @param string     $name   Collection name
 * @param ElggEntity $target Target entity
 * @param array      $params Request parameters
 *
 * @return \hypeJunction\Lists\CollectionInterface|null
 */
function elgg_get_collection($name, ElggEntity $target = null, array $params = []) {
	return elgg()->collections->build($name, $target, $params);
}

/**
 * View a collection
 *
 * @param string          $name   Collection name
 * @param ElggEntity|null $target Target entity
 * @param array           $params Request params
 * @param array           $vars   View vars
 *
 * @return mixed
 */
function elgg_view_collection($name, ElggEntity $target = null, array $params = [], array $vars = []) {
	$collection = elgg_get_collection($name, $target, $params);

	$vars['collection'] = $collection;

	return elgg_view('collection/view', $vars);
}

return function () {
	elgg_register_event_handler('init', 'system', function () {
		$defaults = [
			'page/components/list',
			'page/components/gallery',
			'page/components/ajax_list',
		];

		$views = elgg_trigger_plugin_hook('get_views', 'framework:lists', null, $defaults);
		foreach ($views as $view) {
			elgg_register_plugin_hook_handler('view', $view, 'hypelists_wrap_list_view_hook');
			elgg_register_plugin_hook_handler('view_vars', $view, 'hypelists_filter_vars');
		}

		elgg_extend_view('elgg.css', 'collection/view.css');
		elgg_extend_view('elgg.css', 'forms/collection/search.css');

		elgg_register_plugin_hook_handler('adapter:entity', 'all', [\hypeJunction\Data\Extender::class, 'addData']);
		elgg_register_plugin_hook_handler('adapter:entity', 'all', [
			\hypeJunction\Data\Extender::class,
			'addPermissions'
		]);
		elgg_register_plugin_hook_handler('adapter:entity', 'all', [\hypeJunction\Data\Extender::class, 'addCounters']);
		elgg_register_plugin_hook_handler('adapter:entity', 'all', [
			\hypeJunction\Data\Extender::class,
			'addDataLinks'
		]);

		elgg_register_plugin_hook_handler('adapter:entity', 'user', [
			\hypeJunction\Data\Extender::class,
			'addUserData'
		]);
		elgg_register_plugin_hook_handler('adapter:entity', 'group', [
			\hypeJunction\Data\Extender::class,
			'addGroupData'
		]);
		elgg_register_plugin_hook_handler('adapter:entity', 'object', [
			\hypeJunction\Data\Extender::class,
			'addObjectData'
		]);

		elgg_register_collection('collection:default', \hypeJunction\Lists\DefaultEntityCollection::class);

	});
};
