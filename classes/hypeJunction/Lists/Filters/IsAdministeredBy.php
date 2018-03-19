<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsAdministeredBy implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_administered_by';
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
				->andWhere($qb->compare('relationship', '=', 'group_admin', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', "$from_alias.guid"));

			return $qb->merge([
				$qb->compare("$from_alias.owner_guid", '=', $target, ELGG_VALUE_GUID),
				"EXISTS ({$sub->getSQL()})"
			], 'OR');
		};

		return new WhereClause($filter);
	}
}