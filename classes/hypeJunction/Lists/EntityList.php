<?php

namespace hypeJunction\Lists;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\Entities;
use Elgg\Database\QueryBuilder;
use ElggEntity;

/**
 * Elgg entities query builder with sorting, filtering, and search support.
 */
class EntityList extends Entities {

	/**
	 * {@inheritdoc}
	 */
	public function addSort($class, $direction = null) {

		if (!is_subclass_of($class, SorterInterface::class)) {
			throw new \InvalidArgumentException($class . ' must implement ' . SorterInterface::class);
		}

		/* @var $class SorterInterface */

		$where = $class::build($direction);
		if ($where) {
			$this->options->where($where);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSearchQuery($query = '') {

		$fields = [
			'attributes' => [],
			'metadata' => [
				'name',
				'title'
			],
			'annotations' => [],
			'private_settings' => [],
		];

		$options = $this->options->getArrayCopy();

		$types = $this->options->type_subtype_pairs;
		if (count($types) === 1) {
			$type = array_key_first($types);
			$fields = elgg_trigger_event_results('search:fields', $type, $options, $fields);
		}

		$query = strip_tags((string) $query);
		$query = trim($query);

		$words = preg_split('/\s+/', $query);
		$words = array_map(function ($e) {
			return trim($e);
		}, $words);

		$query_parts = array_unique(array_filter($words));

		$query = function (QueryBuilder $qb, $alias) use ($fields, $query_parts) {
			return _elgg_services()->search->buildSearchWhereQuery($qb, $alias, $fields, $query_parts);
		};

		$this->options->where(new WhereClause($query));

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilter($class, ElggEntity $target = null, array $params = []) {
		if (!is_subclass_of($class, FilterInterface::class)) {
			throw new \InvalidArgumentException($class . ' must implement ' . FilterInterface::class);
		}

		/* @var $class FilterInterface */

		$where = $class::build($target, $params);

		if ($where) {
			$this->options->where($where);
		}

		return $this;
	}

	/**
	 * Get options
	 * @return \Elgg\Database\QueryOptions
	 */
	public function getOptions() {
		return $this->options;
	}
}
