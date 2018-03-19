<?php

namespace hypeJunction\Lists\SearchFields;

class RelationshipToViewer extends SearchField {

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'relationship';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getField() {

		$filter_options = $this->collection->getFilterOptions();
		if (empty($filter_options)) {
			return null;
		}

		$filter_options_values = ['' => ''];
		foreach ($filter_options as $id => $filter_option) {
			$target = $this->collection->getTarget() ? : elgg_get_logged_in_user_entity();
			$filter_options_values[$id] = elgg_echo("sort:{$this->collection->getType()}:filter:$id", [
				$target ? $target->getDisplayName() : ''
			]);
		}

		return [
			'#type' => 'select',
			'#label' => elgg_echo("sort:{$this->collection->getType()}:filter:label"),
			'placeholder' => elgg_echo("sort:{$this->collection->getType()}:filter:placeholder"),
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'options_values' => $filter_options_values,
			'config' => [
				'allowClear' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConstraints() {
		$filter = $this->getValue();
		if (!$filter) {
			return;
		}

		$filters = $this->collection->getFilterOptions();
		$class = elgg_extract($filter, $filters);

		$user = elgg_get_logged_in_user_entity();
		$this->collection->addFilter($class, $user);
	}
}