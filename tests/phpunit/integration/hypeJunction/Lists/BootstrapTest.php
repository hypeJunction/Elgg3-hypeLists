<?php

namespace hypeJunction\Lists;

use Elgg\IntegrationTestCase;
use hypeJunction\Data\DataController;
use hypeJunction\Data\Extender;

/**
 * Characterization suite for hypelists on Elgg 5.x.
 *
 * hypelists is a query/filter/collection framework — 49 classes, no
 * entity subtypes. Test surface is plugin lifecycle, class autoloading
 * (sampling the core abstractions + a handful of filters/sorters/search
 * fields so a missing subclass is caught early), the 7 adapter:entity
 * hook wires, the elgg_register_collection call for collection:default,
 * and instantiation of the default collection through the DI container.
 */
class BootstrapTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypelists';
	}

	public function up() {}
	public function down() {}

	// --- plugin lifecycle ---

	public function testPluginIsRegistered() {
		$this->assertInstanceOf(\ElggPlugin::class, elgg_get_plugin_from_id('hypelists'));
	}

	public function testPluginIsEnabled() {
		$this->assertTrue(elgg_get_plugin_from_id('hypelists')->isEnabled());
	}

	public function testPluginIsActive() {
		$this->assertTrue(elgg_get_plugin_from_id('hypelists')->isActive());
	}

	// --- core class autoloading (Bootstrap + collection framework) ---

	public function testBootstrapClassLoads() {
		$this->assertTrue(class_exists(Bootstrap::class));
	}

	public function testCollectionAbstractLoads() {
		$this->assertTrue(class_exists(Collection::class));
		$r = new \ReflectionClass(Collection::class);
		$this->assertTrue($r->isAbstract());
	}

	public function testCollectionInterfaceLoads() {
		$this->assertTrue(interface_exists(CollectionInterface::class));
	}

	public function testDefaultEntityCollectionLoads() {
		$this->assertTrue(class_exists(DefaultEntityCollection::class));
		$r = new \ReflectionClass(DefaultEntityCollection::class);
		$this->assertTrue($r->isSubclassOf(Collection::class));
		$this->assertTrue($r->implementsInterface(CollectionInterface::class));
	}

	public function testEntityListLoads() {
		$this->assertTrue(class_exists(EntityList::class));
	}

	public function testCollectionsContainerServiceLoads() {
		$this->assertTrue(class_exists(Collections::class));
	}

	public function testFilterInterfaceLoads() {
		$this->assertTrue(interface_exists(FilterInterface::class));
	}

	// --- filter class sampling (20 filters — pick a representative set) ---

	public function testAllFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\All'));
	}

	public function testIsOwnedByFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\IsOwnedBy'));
	}

	public function testIsContainedByFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\IsContainedBy'));
	}

	public function testIsFriendFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\IsFriend'));
	}

	public function testIsMemberFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\IsMember'));
	}

	public function testCreatedBetweenFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\CreatedBetween'));
	}

	public function testSubtypeFilterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Lists\\Filters\\SubtypeFilter'));
	}

	// --- Data namespace classes ---

	public function testDataControllerLoads() {
		$this->assertTrue(class_exists(DataController::class));
	}

	public function testDataExtenderLoads() {
		$this->assertTrue(class_exists(Extender::class));
	}

	public function testCollectionItemAdapterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Data\\CollectionItemAdapter'));
	}

	public function testElggMenuItemAdapterLoads() {
		$this->assertTrue(class_exists('hypeJunction\\Data\\ElggMenuItemAdapter'));
	}

	// --- adapter:entity hook wiring (7 handlers from Bootstrap::init) ---

	public function testAdapterEntityAllHookWired() {
		$handlers = _elgg_services()->events->getAllHandlers();
		$this->assertArrayHasKey('adapter:entity', $handlers);
		$this->assertArrayHasKey('all', $handlers['adapter:entity']);
	}

	public function testAdapterEntityUserHookWired() {
		$handlers = _elgg_services()->events->getAllHandlers();
		$this->assertArrayHasKey('adapter:entity', $handlers);
		$this->assertArrayHasKey('user', $handlers['adapter:entity']);
	}

	public function testAdapterEntityGroupHookWired() {
		$handlers = _elgg_services()->events->getAllHandlers();
		$this->assertArrayHasKey('adapter:entity', $handlers);
		$this->assertArrayHasKey('group', $handlers['adapter:entity']);
	}

	public function testAdapterEntityObjectHookWired() {
		$handlers = _elgg_services()->events->getAllHandlers();
		$this->assertArrayHasKey('adapter:entity', $handlers);
		$this->assertArrayHasKey('object', $handlers['adapter:entity']);
	}

	// --- elgg_register_collection helper + collections DI service ---

	public function testElggRegisterCollectionFunctionExists() {
		$this->assertTrue(function_exists('elgg_register_collection'));
	}

	public function testElggGetCollectionFunctionExists() {
		$this->assertTrue(function_exists('elgg_get_collection'));
	}

	public function testElggViewCollectionFunctionExists() {
		$this->assertTrue(function_exists('elgg_view_collection'));
	}

	public function testCollectionsServiceIsBoundOnElggContainer() {
		$this->assertTrue(elgg()->has('collections'));
		$this->assertInstanceOf(Collections::class, elgg()->collections);
	}

	public function testDefaultCollectionRegisteredAndBuildable() {
		// Bootstrap::init ends with
		//   elgg_register_collection('collection:default', DefaultEntityCollection::class)
		// so elgg_get_collection('collection:default') should produce an
		// instance of DefaultEntityCollection.
		$collection = elgg_get_collection('collection:default');
		$this->assertInstanceOf(DefaultEntityCollection::class, $collection);
		$this->assertInstanceOf(CollectionInterface::class, $collection);
	}

	public function testDefaultEntityCollectionIdMatchesRegisteredName() {
		$collection = elgg_get_collection('collection:default');
		$this->assertSame('collection:default', $collection->getId());
	}
}
