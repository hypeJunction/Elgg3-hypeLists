<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class TimeCreated implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'time_created';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias) use ($direction) {
			$field = "CAST($from_alias.time_created AS UNSIGNED)";
			$qb->orderBy($field, $direction);
		};

		return new WhereClause($sorter);
	}
}