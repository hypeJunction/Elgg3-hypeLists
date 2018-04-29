<?php

namespace hypeJunction\Lists\SearchFields;

use hypeJunction\Lists\FilterInterface;
use hypeJunction\Lists\SorterInterface;

class Sort extends SearchField {

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'sort';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getField() {

		$sort_options = $this->collection->getSortOptions();
		if (!$sort_options) {
			return null;
		}

		$sort_options_values = [];
		foreach ($sort_options as $class) {
			if (!is_subclass_of($class, SorterInterface::class)) {
				throw new \InvalidArgumentException($class . ' must implement ' . SorterInterface::class);
			}

			$id = $class::id();
			foreach (['asc', 'desc'] as $direction) {
				$sort_options_values["$id::$direction"] = elgg_echo("sort:{$this->collection->getType()}:{$id}::{$direction}");
			}
		}

		return [
			'#type' => 'select',
			'#label' => elgg_echo("sort:{$this->collection->getType()}:label"),
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'options_values' => $sort_options_values,
			'placeholder' => elgg_echo("sort:{$this->collection->getType()}:placeholder"),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue() {
		$value = parent::getValue();
		if (!$value) {
			$value = 'time_created::desc';
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConstraints() {
		$sort = $this->getValue();

		if (!$sort) {
			return;
		}

		list($field, $direction) = explode('::', $sort);

		$sorts = $this->collection->getSortOptions();

		$class = false;

		foreach ($sorts as $sort) {
			if (!is_subclass_of($sort, SorterInterface::class)) {
				continue;
			}

			$id = $sort::id();

			if ($id === $field) {
				$this->collection->addSort($sort, $direction);
			}
		}
	}
}