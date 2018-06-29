<?php

namespace hypeJunction\Lists\SearchFields;

use hypeJunction\Lists\FilterInterface;
use hypeJunction\Lists\Filters\IsOwnedBy;

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
		foreach ($filter_options as $filter_option) {
			if (!is_subclass_of($filter_option, FilterInterface::class)) {
				continue;
			}

			$id = $filter_option::id();

			$target = $this->collection->getTarget() ? : elgg_get_logged_in_user_entity();

			$filter_options_values[$id] = elgg_echo("sort:{$this->collection->getType()}:filter:$id", [
				$target ? $target->getDisplayName() : ''
			]);
		}

		$value = $this->getValue();

		$fields = [
			[
				'#type' => 'select',
				'placeholder' => elgg_echo("sort:{$this->collection->getType()}:filter:placeholder"),
				'name' => $this->getName() . '[relationship]',
				'value' => elgg_extract('relationship', $value),
				'options_values' => $filter_options_values,
				'config' => [
					'allowClear' => true,
				],
			],
		];

		if ($this->collection->getType() !== 'user') {
			$fields[] = [
				'#type' => 'guids',
				'options' => [
					'type' => 'user',
				],
				'placeholder' => elgg_echo("sort:object:filter:placeholder:guids"),
				'name' => $this->getName() . '[guids]',
				'value' => elgg_extract('guids', $value),
				'multiple' => true,
			];
		}

		if (sizeof($fields) == 1) {
			$field = $fields[0];
			$field['#label'] = elgg_echo("sort:{$this->collection->getType()}:filter:label");

			return $field;
		}

		return [
			'#type' => 'fieldset',
			'#label' => elgg_echo("sort:{$this->collection->getType()}:filter:label"),
			'fields' => $fields,
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

		$relationship = elgg_extract('relationship', $filter);

		$filter_options = $this->collection->getFilterOptions();

		foreach ($filter_options as $filter_option) {
			if (!is_subclass_of($filter_option, FilterInterface::class)) {
				continue;
			}

			if ($filter_option::id() === $relationship) {
				$user = elgg_get_logged_in_user_entity();
				$this->collection->addFilter($filter_option, $user);
			}
		}

		$guids = elgg_extract('guids', $filter);
		if (!empty($guids)) {
			$this->collection->addFilter(IsOwnedBy::class, null, [
				'guids' => $guids,
			]);
		}
	}
}