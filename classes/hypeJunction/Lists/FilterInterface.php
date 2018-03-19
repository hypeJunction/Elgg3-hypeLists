<?php

namespace hypeJunction\Lists;

use Elgg\Database\Clauses\WhereClause;

interface FilterInterface {

	/**
	 * Returns ID of the filter
	 * @return string
	 */
	public static function id();

	/**
	 * Build a filtering clause
	 *
	 * @param       $target \ElggEntity Target entity of the filtering relationship
	 * @param array $params Filter params
	 *
	 * @return WhereClause|null
	 */
	public static function build(\ElggEntity $target = null, array $params = []);
}