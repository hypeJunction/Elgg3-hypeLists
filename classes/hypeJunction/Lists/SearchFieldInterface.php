<?php

namespace hypeJunction\Lists;

/** Interface for search form field implementations. */
interface SearchFieldInterface {

	/**
	 * Constructor
	 *
	 * @param CollectionInterface $collection Collection
	 */
	public function __construct(CollectionInterface $collection);

	/**
	 * Returns field name
	 * @return string
	 */
	public function getName();

	/**
	 * Returns field value
	 *
	 * @return mixed
	 */
	public function getValue();

	/**
	 * Returns field parameters
	 *
	 * @return array|null
	 */
	public function getField();

	/**
	 * Set constraints on the collection based on field value
	 * @return void
	 */
	public function setConstraints();
}
