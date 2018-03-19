<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class HasClosedMembership implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'has_closed_membership';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($target) {
			$md_alias = $qb->joinMetadataTable($from_alias, 'guid', 'membership');

			return $qb->compare("$md_alias.value", '!=', ACCESS_PUBLIC, ELGG_VALUE_STRING);
		};

		return new WhereClause($filter);
	}
}