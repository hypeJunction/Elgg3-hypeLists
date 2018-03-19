<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class MemberCount implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'member_count';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias = 'e') use ($direction) {
			$qb->joinRelationshipTable($from_alias, 'guid', 'member', true, 'left', 'member_count');
			$qb->addSelect('COUNT(member_count.guid_two) AS member_count');
			$qb->addGroupBy('e.guid');
			$qb->orderBy('member_count', $direction);
		};

		return new WhereClause($sorter);
	}
}