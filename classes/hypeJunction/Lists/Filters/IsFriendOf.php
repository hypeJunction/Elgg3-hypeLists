<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsFriendOf implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_friend_of';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		if (!isset($target)) {
			return null;
		}

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($target) {
			$sub = $qb->subquery('entity_relationships');
			$sub->select(1)
				->where($qb->compare('guid_two', '=', $target, ELGG_VALUE_GUID))
				->andWhere($qb->compare('relationship', '=', 'friend', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_one', '=', "$from_alias.guid"));

			return "EXISTS ({$sub->getSQL()})";
		};

		return new WhereClause($filter);
	}
}