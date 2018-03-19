<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsContainedByUsersGroups implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_contained_by_users_groups';
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
				->where($qb->compare('guid_one', '=', "$from_alias.container_guid"))
				->andWhere($qb->compare('relationship', '=', 'member', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', $target, ELGG_VALUE_GUID));

			return "EXISTS ({$sub->getSQL()})";
		};

		return new WhereClause($filter);
	}
}