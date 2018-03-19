<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class CreatedBetween implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'created_between';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		$created_after = elgg_extract('created_after', $params);
		$created_before = elgg_extract('created_before', $params);

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($created_after, $created_before) {
			return $qb->between("$from_alias.time_created",$created_after, $created_before, ELGG_VALUE_TIMESTAMP);
		};

		return new WhereClause($filter);
	}
}