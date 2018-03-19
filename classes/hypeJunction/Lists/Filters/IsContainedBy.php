<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsContainedBy implements FilterInterface {


	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_contained_by';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		if (!isset($target)) {
			return null;
		}

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($target) {
			return $qb->compare("$from_alias.container_guid", '=', $target, ELGG_VALUE_GUID);
		};

		return new WhereClause($filter);
	}
}