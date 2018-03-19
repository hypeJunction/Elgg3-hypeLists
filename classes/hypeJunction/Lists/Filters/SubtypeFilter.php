<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class SubtypeFilter implements FilterInterface {

	/**
	 * Returns ID of the filter
	 * @return string
	 */
	public static function id() {
		return 'subtype';
	}

	/**
	 * Build a filtering clause
	 *
	 * @param       $target \ElggEntity Target entity of the filtering relationship
	 * @param array $params Filter params
	 *
	 * @return WhereClause|null
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {
		$subtype = elgg_extract('subtype', $params);

		if (!$subtype) {
			return null;
		}

		$filter = function(QueryBuilder $qb, $from_alias) use ($subtype) {
			return $qb->compare("$from_alias.subtype", '=', $subtype, ELGG_VALUE_STRING);
		};

		return new WhereClause($filter);
	}
}