<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class FriendCount implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'friend_count';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias = 'e') use ($direction) {
			$qb->joinRelationshipTable($from_alias, 'guid', 'friend', false, 'left', 'friend_count');
			$qb->addSelect('COUNT(friend_count.guid_two) AS friend_count');
			$qb->addGroupBy('e.guid');
			$qb->orderBy('friend_count', $direction);
		};

		return new WhereClause($sorter);
	}
}