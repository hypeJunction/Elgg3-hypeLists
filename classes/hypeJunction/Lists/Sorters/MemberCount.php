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
			$qb->joinRelationshipTable($from_alias, 'guid', 'member', false, 'left', 'member');
			$qb->addSelect('COUNT(DISTINCT(member.id)) AS member_count');
			$qb->addGroupBy('member.guid_two');
			$qb->orderBy('member_count', $direction);
		};

		return new WhereClause($sorter);
	}
}