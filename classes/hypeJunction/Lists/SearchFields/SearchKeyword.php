<?php

namespace hypeJunction\Lists\SearchFields;

class SearchKeyword extends SearchField {

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'query';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getField() {
		return [
			'#type' => 'text',
			'#label' => elgg_echo("sort:{$this->collection->getType()}:search:label"),
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'placeholder' => elgg_echo("sort:{$this->collection->getType()}:search:placeholder"),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConstraints() {
		$query = $this->getValue();
		if (!$query) {
			return;
		}

		$this->collection->setSearchQuery($query);
	}
}