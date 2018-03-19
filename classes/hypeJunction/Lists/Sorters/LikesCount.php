<?php

namespace hypeJunction\Lists\Sorters;

use Elgg\Database\Clauses\WhereClause;
use Elgg\Database\QueryBuilder;
use hypeJunction\Lists\SorterInterface;

class LikesCount implements SorterInterface {

	/**
	 * {@inheritdoc}
	 */
	public static function id() {
		return 'likes_count';
	}


	/**
	 * {@inheritdoc}
	 */
	public static function build($direction = null) {

		$sorter = function (QueryBuilder $qb, $from_alias = 'e') use ($direction) {
			$qb->joinAnnotationTable($from_alias, 'guid', 'likes', 'left', 'likes');
			$qb->addSelect('COUNT(likes.id) AS likes_count');
			$qb->addGroupBy('e.guid');
			$qb->orderBy('likes_count', $direction);
		};

		return new WhereClause($sorter);
	}
}