<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsOnline implements FilterInterface {


	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_online';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {
		$filter = function (QueryBuilder $qb, $from_alias = 'e') {
			$dt = strtotime('-10 minutes');

			return $qb->compare("$from_alias.last_action", ">=", $dt, ELGG_VALUE_TIMESTAMP);
		};

		return new WhereClause($filter);
	}
}