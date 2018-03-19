<?php

namespace hypeJunction\Lists\SearchFields;

use hypeJunction\Lists\Filters\SubtypeFilter;

class Subtype extends SearchField {

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'subtype';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getField() {

		$subtypes = (array) $this->collection->getSubtypes();
		if (empty($subtypes) || count($subtypes) <= 1) {
			return null;
		}

		$subtype_options = ['' => ''];
		foreach ($subtypes as $subtype) {
			$subtype_options[$subtype] = elgg_echo("collection:object:$subtype");
		}

		return [
			'#type' => 'select',
			'#label' => elgg_echo("sort:{$this->collection->getType()}:subtype:label"),
			'placeholder' => elgg_echo("sort:{$this->collection->getType()}:subtype:placeholder"),
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'options_values' => $subtype_options,
			'config' => [
				'allowClear' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConstraints() {
		$subtype = $this->getValue();
		if (!$subtype) {
			return;
		}

		$subtypes = $this->collection->getSubtypes();
		if (!in_array($subtype, $subtypes)) {
			return;
		}

		$this->collection->addFilter(SubtypeFilter::class, null, ['subtype' => $subtype]);
	}
}