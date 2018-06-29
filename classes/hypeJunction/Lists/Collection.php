<?php

namespace hypeJunction\Lists;

use ElggEntity;
use hypeJunction\Data\CollectionItemAdapter;
use hypeJunction\Lists\SearchFields\RelationshipToViewer;
use hypeJunction\Lists\SearchFields\SearchKeyword;
use hypeJunction\Lists\SearchFields\Sort;
use Psr\Log\LogLevel;

abstract class Collection implements CollectionInterface {

	/**
	 * @var \ElggEntity
	 */
	protected $target;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @var array
	 */
	protected $sorts = [];

	/**
	 * @var array
	 */
	protected $filters = [];

	/**
	 * @var string
	 */
	protected $query = '';

	/**
	 * Constructor
	 *
	 * @param \ElggEntity $target Target entity of the collection
	 * @param array       $params Request params
	 */
	public function __construct(\ElggEntity $target = null, array $params = []) {
		$this->target = $target;
		$this->params = $params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTarget() {
		return $this->target;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function getList(array $options = []) {
		$options = $this->getQueryOptions($options);

		$options['types'] = $this->getType();
		$options['subtypes'] = $this->getSubtypes();

		if (!isset($options['limit'])) {
			$limit = elgg_extract('limit', $this->params);

			if (!isset($limit)) {
				$list_options = $this->getListOptions();

				$list_type = elgg_extract('list_type', $list_options, 'list');

				$limit = ($list_type == 'gallery') ? 12 : elgg_get_config('default_limit');
			}

			$options['limit'] = $limit;
		}

		if (!isset($options['offset'])) {
			$offset = elgg_extract('offset', $this->params, 0);
			$options['offset'] = $offset;
		}

		$options['limit'] = (int) $options['limit'];
		$options['offset'] = (int) $options['offset'];

		$list = new EntityList($options);

		foreach ($this->sorts as $sort) {
			$list->addSort($sort->class, $sort->direction);
		}

		foreach ($this->filters as $filter) {
			$list->addFilter($filter->class, $filter->target, $filter->params);
		}

		$list->setSearchQuery($this->query);

		return $list;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function addSort($class, $direction = null) {
		$this->sorts[] = (object) [
			'class' => $class,
			'direction' => $direction,
		];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function getSorts() {
		return $this->sorts;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function addFilter($class, ElggEntity $target = null, array $params = []) {
		$this->filters[] = (object) [
			'class' => $class,
			'target' => $target ? : elgg_get_logged_in_user_entity(),
			'params' => $params,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	final public function getFilters() {
		return $this->filters;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function setSearchQuery($query = '') {
		$this->query = $query;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function getSearchQuery() {
		return $this->query;
	}

	/**
	 * {@inheritdoc}
	 */
	final public function render(array $vars = []) {
		$vars = $this->getListOptions($vars);

		$list = $this->getList();

		if (!isset($vars['limit'])) {
			$vars['limit'] = $list->getOptions()->limit;
		}

		if (!isset($vars['offset'])) {
			$vars['offset'] = $list->getOptions()->offset;
		}

		$vars['items'] = $list->get($vars['limit'], $vars['offset']);
		$vars['count'] = $list->count();

		$query = _elgg_services()->request->getParams();
		unset($query['limit']);
		unset($query['offset']);
		unset($query['_route']);

		$vars['base_url'] = elgg_http_add_url_query_elements($this->getURL(), $query);
		$vars['list_id'] = md5($this->getURL());

		return elgg_view('collection/list', $vars);
	}

	/**
	 * {@inheritdoc}
	 */
	final public function export() {

		$viewtype = elgg_get_viewtype();
		elgg_set_viewtype('default');

		$list = $this->getList();

		$limit = $list->getOptions()->limit;
		$offset = $list->getOptions()->offset;

		$batch = $list->batch($limit, $offset);

		$data = [
			'count' => (int) $batch->count(),
			'limit' => (int) $limit,
			'offset' => (int) $offset,
			'items' => [],
			'_related' => [],
		];

		foreach ($batch as $entity) {
			$adapter = new CollectionItemAdapter($entity);
			$data['items'][] = $adapter->export($this->params);

			if ($owner = $entity->getOwnerEntity()) {
				if (!isset($data['_related'][$owner->guid])) {
					$adapter = new CollectionItemAdapter($owner);
					$data['_related'][$owner->guid] = $adapter->export($this->params);
				}
			}

			if ($container = $entity->getContainerEntity()) {
				if (!isset($data['_related'][$container->guid])) {
					$adapter = new CollectionItemAdapter($container);
					$data['_related'][$container->guid] = $adapter->export($this->params);
				}
			}
		}

		$data['_related'] = array_values($data['_related']);

		$url = current_page_url();
		$url = substr($url, strlen(elgg_get_site_url()));
		if ($data['count'] && $offset > 0) {
			$prev_offset = $offset - $limit;
			if ($prev_offset < 0) {
				$prev_offset = 0;
			}

			$data['_links']['prev'] = elgg_http_add_url_query_elements($url, [
				'offset' => $prev_offset,
			]);
		} else {
			$data['_links']['prev'] = false;
		}

		if ($data['count'] > $limit + $offset) {
			$next_offset = $offset + $limit;
			$data['_links']['next'] = elgg_http_add_url_query_elements($url, [
				'offset' => $next_offset,
			]);
		} else {
			$data['_links']['next'] = false;
		}

		elgg_set_viewtype($viewtype);

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortOptions() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterOptions() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchOptions() {
		return [
			Sort::class,
			RelationshipToViewer::class,
			SearchKeyword::class,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	final public function getSearchFields() {
		$classes = $this->getSearchOptions();

		$fields = [];

		foreach ($classes as $class) {
			if (!is_subclass_of($class, SearchFieldInterface::class)) {
				throw new \InvalidArgumentException($class . ' must implement ' . SearchFieldInterface::class);
			}

			/* @var $class \hypeJunction\Lists\SearchFieldInterface */

			$fields[] = new $class($this);
		}

		$params = $this->params;
		$params['collection'] = $this;

		return elgg_trigger_plugin_hook('search:fields', $this->getId(), $params, $fields);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMenu() {

		$type = $this->getType();
		$subtypes = (array) $this->getSubtypes();

		$target = $this->getTarget();
		
		$menu = [];

		foreach ($subtypes as $subtype) {

			$owner = $target;
			
			if (!$owner || ($owner instanceof \ElggUser && $owner->guid != $target->guid)) {
				$owner = elgg_get_logged_in_user_entity();
			}

			if (!$owner) {
				return [];
			}
			
			// do we have an owner and is the current user allowed to create content here
			if (!$owner->canWriteToContainer(0, $type, $subtype)) {
				continue;
			}

			$href = elgg_generate_url("add:$type:$subtype", [
				'guid' => $owner->guid,
			]);

			if (!$href) {
				continue;
			}

			$text = elgg_echo("add:$type:$subtype");

			// register the title menu item
			$menu[] = \ElggMenuItem::factory([
				'name' => 'add',
				'icon' => 'plus',
				'href' => $href,
				'text' => $text,
			]);
		}

		return $menu;
	}
}