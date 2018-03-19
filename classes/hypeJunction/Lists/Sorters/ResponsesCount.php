<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class ResponsesCount implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'responses_count';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias = 'e') use ($direction) {
			$condition = $qb->merge([
				$qb->compare('responses.container_guid', '=', "$from_alias.guid"),
				$qb->compare('responses.type', '=', 'object', ELGG_VALUE_STRING),
				$qb->compare('responses.subtype', '=', 'comment', ELGG_VALUE_STRING),
			]);

			$qb->leftJoin($from_alias, $qb->prefix('entities'), 'responses', $condition);

			$qb->addSelect('COUNT(responses.guid) AS responses_count');

			$qb->addGroupBy('e.guid');

			$qb->orderBy('responses_count', $direction);
		};

		return new WhereClause($sorter);
	}
}