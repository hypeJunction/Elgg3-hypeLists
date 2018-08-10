<?php
/**
 *
 */

namespace hypeJunction\Lists\SearchFields;

use hypeJunction\Lists\CollectionInterface;
use hypeJunction\Lists\SearchFieldInterface;

abstract class SearchField implements SearchFieldInterface {

	/**
	 * @var CollectionInterface
	 */
	protected $collection;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(CollectionInterface $collection) {
		$this->collection = $collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue() {
		return elgg_extract($this->getName(), $this->collection->getParams());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCollection() {
		return $this->collection;
	}
}