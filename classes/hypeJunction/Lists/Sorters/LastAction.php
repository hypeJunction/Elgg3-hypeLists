<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class LastAction implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'last_action';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias = 'e') use ($direction) {
			$qb->addSelect("GREATEST ($from_alias.time_created, $from_alias.last_action, $from_alias.time_updated) AS last_action");
			$qb->orderBy('last_action', $direction);
		};

		return new WhereClause($sorter);
	}
}