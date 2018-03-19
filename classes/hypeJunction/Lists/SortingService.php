<?php

namespace hypeJunction\Lists;

use Elgg\Database\Clauses\WhereClause;
use ElggEntity;
use hypeJunction\Lists\Sorters\TimeCreated;
use hypeJunction\Lists\Sorters\Metadata;
use InvalidParameterException;

/**
 * Sorting
 */
class SortingService {

	/**
	 * @var array
	 */
	protected $sorters = [];

	/**
	 * Register a new sorter
	 *
	 * @param string $name   Sorter name
	 * @param string $sorter Sorter class name (must implement Sorter interface)
	 *
	 * @return void
	 */
	public function register($name, $sorter) {
		$this->sorters[$name] = $sorter;
	}

	/**
	 * Unregister a sorter
	 *
	 * @param string $name Sorter name
	 *
	 * @return void
	 */
	public function unregister($name) {
		unset($this->sorters[$name]);
	}

	/**
	 * Returns all registered sorters
	 * @return array
	 */
	public function all() {
		return $this->sorters;
	}

	/**
	 * Build a sorting filter
	 *
	 * @param string $name      Sort name
	 * @param string $direction Sort direction
	 * @param string $as        SQL value type to cast to (UNSIGNED, SIGNED, DATE etc) for comparison
	 *
	 * @return WhereClause|null
	 * @throws InvalidParameterException
	 */
	public function build($name, $direction = '', $as = null) {

		if (isset($this->sorters[$name])) {
			$sorter = $this->sorters[$name];
		} else if (in_array($name, ElggEntity::$primary_attr_names)) {
			$sorter = TimeCreated::class;
		} else {
			$sorter = Metadata::class;
		}

		if (!is_subclass_of($sorter, SorterInterface::class)) {
			throw new InvalidParameterException('Sorters must implement ' . SorterInterface::class . ' interface');
		}

		$sorter = new $sorter();

		/* @var $sorter \hypeJunction\Lists\SorterInterface */

		return $sorter->build($name, $direction, $as);
	}
}