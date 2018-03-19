<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use Elgg\TimeUsing;
use hypeJunction\Lists\FilterInterface;

class HasOpenMembership implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'has_open_membership';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($target) {
			$md_alias = $qb->joinMetadataTable($from_alias, 'guid', 'membership');

			return $qb->compare("$md_alias.value", '=', ACCESS_PUBLIC, ELGG_VALUE_STRING);
		};

		return new WhereClause($filter);
	}
}