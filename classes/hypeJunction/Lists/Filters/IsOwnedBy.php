<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsOwnedBy implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_owned_by';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		$targets = elgg_extract('guids', $params, []);

		if (empty($targets)) {
			$targets[] = $target;
		}

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($targets) {
			return $qb->compare("$from_alias.owner_guid", '=', $targets, ELGG_VALUE_GUID);
		};

		return new WhereClause($filter);
	}
}