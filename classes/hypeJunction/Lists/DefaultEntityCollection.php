<?php

namespace hypeJunction\Lists;

use hypeJunction\Lists\SearchFields\CreatedBetween;
use hypeJunction\Lists\Sorters\Alpha;
use hypeJunction\Lists\Sorters\FriendCount;
use hypeJunction\Lists\Sorters\LastAction;
use hypeJunction\Lists\Sorters\LikesCount;
use hypeJunction\Lists\Sorters\MemberCount;
use hypeJunction\Lists\Sorters\ResponsesCount;
use hypeJunction\Lists\Sorters\TimeCreated;

class DefaultEntityCollection extends Collection {

	/**
	 * Get ID of the collection
	 * @return string
	 */
	public function getId() {
		return 'collection:default';
	}

	/**
	 * Get title of the collection
	 * @return string
	 */
	public function getDisplayName() {
		return elgg_echo('collection:default');
	}

	/**
	 * Get the type of collection, e.g. owner, friends, group all
	 * @return string
	 */
	public function getCollectionType() {
		return 'default';
	}

	/**
	 * Get type of entities in the collection
	 * @return mixed
	 */
	public function getType() {
		return elgg_extract('types', $this->params);
	}

	/**
	 * Get subtypes of entities in the collection
	 * @return string|string[]
	 */
	public function getSubtypes() {
		return elgg_extract('subtypes', $this->params);
	}

	/**
	 * Get default query options
	 *
	 * @param array $options Query options
	 *
	 * @return array
	 */
	public function getQueryOptions(array $options = []) {
		return array_merge($this->params, $options);
	}

	/**
	 * Get default list view options
	 *
	 * @param array $options List view options
	 *
	 * @return mixed
	 */
	public function getListOptions(array $options = []) {
		return array_merge($this->params, $options);
	}

	/**
	 * Returns base URL of the collection
	 *
	 * @return string
	 */
	public function getURL() {
		return current_page_url();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortOptions() {
		return [
			Alpha::class,
			TimeCreated::class,
			LastAction::class,
			LikesCount::class,
			FriendCount::class,
			MemberCount::class,
			ResponsesCount::class,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchOptions() {
		$fields = parent::getSearchOptions();

		$fields[] = CreatedBetween::class;

		return $fields;
	}
}