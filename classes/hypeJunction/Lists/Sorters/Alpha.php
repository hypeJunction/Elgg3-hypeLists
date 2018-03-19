<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class Alpha implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'alpha';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias = 'e') use ($direction) {
			$md_alias = $qb->joinMetadataTable($from_alias, 'guid', ['name', 'title']);
			$qb->addOrderBy("$md_alias.value", $direction);
		};

		return new WhereClause($sorter);
	}
}