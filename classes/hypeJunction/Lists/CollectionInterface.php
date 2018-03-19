<?php

namespace hypeJunction\Lists;

use Elgg\Database\Repository;
use ElggEntity;

interface CollectionInterface {

	/**
	 * Constructor
	 *
	 * @param \ElggEntity $target Target entity of the collection
	 * @param array       $params Request params
	 */
	public function __construct(\ElggEntity $target = null, array $params = []);

	/**
	 * Get ID of the collection
	 * @return string
	 */
	public function getId();

	/**
	 * Get title of the collection
	 * @return string
	 */
	public function getDisplayName();

	/**
	 * Get the type of collection, e.g. owner, friends, group all
	 * @return string
	 */
	public function getCollectionType();

	/**
	 * Get type of entities in the collection
	 * @return mixed
	 */
	public function getType();

	/**
	 * Get subtypes of entities in the collection
	 * @return string|string[]
	 */
	public function getSubtypes();

	/**
	 * Get a list of entities in the collection
	 *
	 * @param array $options Query options
	 *
	 * @return Repository
	 */
	public function getList(array $options = []);

	/**
	 * Get default query options
	 *
	 * @param array $options Query options
	 *
	 * @return array
	 */
	public function getQueryOptions(array $options = []);

	/**
	 * Get default list view options
	 *
	 * @param array $options List view options
	 *
	 * @return mixed
	 */
	public function getListOptions(array $options = []);

	/**
	 * Get available sort options
	 * Must return a list of classes implementing SortingInterface
	 * @return array
	 */
	public function getSortOptions();

	/**
	 * Get available relationship filter options
	 * Must return a list of classes implementing FilterInterface
	 * @return array
	 */
	public function getFilterOptions();

	/**
	 * Get a list of fields to display in the search form
	 * Must return a list of classes implementing SearchFieldInterface
	 * @return array
	 */
	public function getSearchOptions();

	/**
	 * Build search fields from search options
	 *
	 * @param array $params Request params
	 *
	 * @return SearchFieldInterface[]
	 */
	public function getSearchFields();

	/**
	 * Returns base URL of the collection
	 *
	 * @return string
	 */
	public function getURL();

	/**
	 * Get target entity of the collection
	 *
	 * @return ElggEntity|null
	 */
	public function getTarget();

	/**
	 * Get collection params
	 *
	 * @return array
	 */
	public function getParams();

	/**
	 * Add a filter
	 *
	 * @param string     $class  Filter class
	 * @param ElggEntity $target Target entity
	 * @param array      $params Additional params
	 *
	 * @return static
	 */
	public function addFilter($class, ElggEntity $target = null, array $params = []);

	/**
	 * Get filters
	 *
	 * @return array
	 */
	public function getFilters();

	/**
	 * Set sorter and direction
	 *
	 * @param string $class     Sorter class
	 * @param string $direction Sort direction
	 *
	 * @return static
	 */
	public function addSort($class, $direction = null);

	/**
	 * Get sorts
	 * @return array
	 */
	public function getSorts();

	/**
	 * Reduce the list to items matching a serch query
	 *
	 * @param string $query Search query
	 *
	 * @return static
	 */
	public function setSearchQuery($query = '');

	/**
	 * Get search query
	 *
	 * @return string
	 */
	public function getSearchQuery();

	/**
	 * Render a list
	 *
	 * @param array $vars View vars
	 *
	 * @return mixed
	 */
	public function render(array $vars = []);

	/**
	 * Export a list into a serializable object
	 *
	 * @param array $params Export params
	 *
	 * @return mixed
	 */
	public function export();

	/**
	 * Get menu items related to the collection
	 * @return \ElggMenuItem[]
	 */
	public function getMenu();
}