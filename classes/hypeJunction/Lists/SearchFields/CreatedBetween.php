<?php

namespace hypeJunction\Lists\SearchFields;

class CreatedBetween extends SearchField {

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'created_between';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getField() {
		$name = $this->getName();
		$value = $this->getValue() ? : [];

		return [
			'#type' => 'fieldset',
			'fields' => [
				[
					'#type' => 'date',
					'#label' => elgg_echo("sort:{$this->collection->getType()}:search:created_after"),
					'timestamp' => true,
					'name' => "{$name}[created_after]",
					'value' => elgg_extract('created_after', $value),
				],
				[
					'#type' => 'date',
					'#label' => elgg_echo("sort:{$this->collection->getType()}:search:created_before"),
					'timestamp' => true,
					'name' => "{$name}[created_before]",
					'value' => elgg_extract('created_before', $value),
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConstraints() {
		$value = $this->getValue();
		if (!$value) {
			return;
		}

		$this->collection->addFilter(\hypeJunction\Lists\Filters\CreatedBetween::class, null, $value);
	}
}