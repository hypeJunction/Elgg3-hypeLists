<?php

namespace hypeJunction\Lists\Filters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\FilterInterface;

class IsFeatured implements FilterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'is_featured';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build(\ElggEntity $target = null, array $params = []) {

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($target) {
			$md_alias = $qb->joinMetadataTable($from_alias, 'guid', ['featured', 'featured_group']);

			return $qb->compare("$md_alias.value", '=', ['yes', '1'], ELGG_VALUE_STRING);
		};

		return new WhereClause($filter);
	}
}