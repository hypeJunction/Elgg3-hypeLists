<?php

namespace hypeJunction\Lists;

use Elgg\Database\Clauses\WhereClause;

interface SorterInterface {

	/**
	 * Returns ID of the sorter
	 * @return string
	 */
	public static function id();

	/**
	 * Build a sorting clause
	 *
	 * @param string $direction Sort direction
	 *
	 * @return WhereClause|null
	 */
	public static function build($direction = null);
}