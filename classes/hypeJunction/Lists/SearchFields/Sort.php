<?php

namespace hypeJunction\Lists\SearchFields;

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
		foreach ($sort_options as $id =>$class) {
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

		$class = $sorts[$field];

		if ($class) {
			$this->collection->addSort($class, $direction);
		}

	}
}