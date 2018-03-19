<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsNotFriend implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_not_friend';
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
				->where($qb->compare('guid_one', '=', $target, ELGG_VALUE_GUID))
				->andWhere($qb->compare('relationship', '=', 'friend', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', "$from_alias.guid"));

			return "NOT EXISTS ({$sub->getSQL()})";
		};

		return new WhereClause($filter);
	}
}