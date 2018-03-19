<?php

$type = elgg_extract('type', $vars);
$subtype = elgg_extract('subtype', $vars);

if (!$type || !$subtype) {
	return;
}

$widget = elgg_extract('entity', $vars);

switch ($widget->context) {
	case 'dashboard' :
		$target = null;
		$collection = "collection:$type:$subtype:all";
		break;

	default :
		$target = $widget->getOwnerEntity();
		if ($target instanceof ElggUser) {
			$collection = "collection:$type:$subtype:owner";
		} else {
			$collection = "collection:$type:$subtype:group";
		}
		break;

}

$params = $vars;
$params['limit'] = $widget->num_display ? : 4;
$params['full_view'] = false;
$params['list_type'] = 'list';
$params['pagination'] = false;

$collection = elgg_get_collection($collection, $target, $params);

$count = $collection->getList()->count();

if (empty($count)) {
	echo elgg_format_element('p', [
		'class' => 'elgg-no-results',
	], elgg_echo('collection:no_results'));

	return;
}

echo $collection->render($params);

$items = $collection->getMenu();

$items[] = ElggMenuItem::factory([
	'name' => 'all',
	'href' => $collection->getURL(),
	'text' => elgg_echo('collection:more'),
	'icon_alt' => 'caret-right',
]);

$menu = elgg_view_menu('widget:more', [
	'entity' => $widget,
	'collection' => $collection,
	'items' => $items,
	'class' => 'elgg-menu-hz elgg-menu-entity',
]);

if ($menu) {
	echo elgg_format_element('div', [
		'class' => 'elgg-widget-more',
	], $menu);
}
