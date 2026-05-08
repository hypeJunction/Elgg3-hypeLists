<?php

namespace hypeJunction\Lists;

use Elgg\Database\Clauses\WhereClause;
use Elgg\IntegrationTestCase;
use hypeJunction\Lists\Filters\All;
use hypeJunction\Lists\Filters\CreatedBetween;
use hypeJunction\Lists\Filters\IsContainedBy;
use hypeJunction\Lists\Filters\IsOwnedBy;
use hypeJunction\Lists\Filters\SubtypeFilter;
use hypeJunction\Lists\Sorters\Alpha;
use hypeJunction\Lists\Sorters\LastAction;
use hypeJunction\Lists\Sorters\LikesCount;
use hypeJunction\Lists\Sorters\MemberCount;
use hypeJunction\Lists\Sorters\ResponsesCount;
use hypeJunction\Lists\Sorters\TimeCreated;
use hypeJunction\Lists\SearchFields\CreatedBetween as CreatedBetweenField;
use Elgg\Exceptions\InvalidParameterException;

/**
 * Behavior tests for the hypelists Collections service, DefaultEntityCollection,
 * EntityList, filter and sorter classes.
 */
class CollectionsTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypelists';
	}

	public function up(): void {}
	public function down(): void {}

	// --- Collections service ---

	public function testCollectionsBuildReturnsNullForUnregisteredName(): void {
		$service = elgg()->collections;
		$this->assertNull($service->build('collection:unknown-' . uniqid()));
	}

	public function testCollectionsRegisterAndBuildReturnsCollectionInstance(): void {
		$service = elgg()->collections;
		$name = 'collection:test-' . uniqid();
		$service->register($name, DefaultEntityCollection::class);

		$collection = $service->build($name);

		$this->assertInstanceOf(CollectionInterface::class, $collection);
		$this->assertInstanceOf(DefaultEntityCollection::class, $collection);
	}

	public function testCollectionsBuildThrowsForNonCollectionClass(): void {
		$service = elgg()->collections;
		$name = 'collection:bad-' . uniqid();
		$service->register($name, \stdClass::class);

		$this->expectException(InvalidParameterException::class);
		$service->build($name);
	}

	public function testCollectionsBuildPassesTargetAndParams(): void {
		$service = elgg()->collections;
		$name = 'collection:params-' . uniqid();
		$service->register($name, DefaultEntityCollection::class);

		$user = $this->createUser();
		try {
			$collection = $service->build($name, $user, ['types' => 'object', 'subtypes' => 'blog']);
			$this->assertSame($user->guid, $collection->getTarget()->guid);
			$this->assertSame('object', $collection->getType());
			$this->assertSame('blog', $collection->getSubtypes());
		} finally {
			$user->delete();
		}
	}

	// --- elgg_register_collection / elgg_get_collection globals ---

	public function testElggGetCollectionReturnsNullForUnregisteredName(): void {
		$this->assertNull(elgg_get_collection('collection:never-registered-' . uniqid()));
	}

	public function testElggRegisterCollectionAndGetCollection(): void {
		$name = 'collection:global-' . uniqid();
		elgg_register_collection($name, DefaultEntityCollection::class);

		$collection = elgg_get_collection($name);

		$this->assertInstanceOf(CollectionInterface::class, $collection);
		$this->assertInstanceOf(DefaultEntityCollection::class, $collection);
	}

	// --- DefaultEntityCollection identity ---

	public function testDefaultEntityCollectionGetId(): void {
		$c = new DefaultEntityCollection();
		$this->assertSame('collection:default', $c->getId());
	}

	public function testDefaultEntityCollectionGetCollectionType(): void {
		$c = new DefaultEntityCollection();
		$this->assertSame('default', $c->getCollectionType());
	}

	public function testDefaultEntityCollectionGetTypeFromParams(): void {
		$c = new DefaultEntityCollection(null, ['types' => 'user']);
		$this->assertSame('user', $c->getType());
	}

	public function testDefaultEntityCollectionGetSubtypesFromParams(): void {
		$c = new DefaultEntityCollection(null, ['subtypes' => 'blog']);
		$this->assertSame('blog', $c->getSubtypes());
	}

	public function testDefaultEntityCollectionGetTargetReturnsPassedEntity(): void {
		$user = $this->createUser();
		try {
			$c = new DefaultEntityCollection($user);
			$this->assertSame($user->guid, $c->getTarget()->guid);
		} finally {
			$user->delete();
		}
	}

	public function testDefaultEntityCollectionGetParamsReturnsConstructorParams(): void {
		$params = ['types' => 'object', 'limit' => 20];
		$c = new DefaultEntityCollection(null, $params);
		$this->assertSame($params, $c->getParams());
	}

	// --- Collection search query ---

	public function testSetAndGetSearchQueryRoundtrip(): void {
		$c = new DefaultEntityCollection();
		$c->setSearchQuery('hello world');
		$this->assertSame('hello world', $c->getSearchQuery());
	}

	public function testDefaultSearchQueryIsEmpty(): void {
		$c = new DefaultEntityCollection();
		$this->assertSame('', $c->getSearchQuery());
	}

	// --- Collection sorts ---

	public function testAddSortAccumulatesSorts(): void {
		$c = new DefaultEntityCollection();
		$c->addSort(TimeCreated::class, 'desc');
		$c->addSort(Alpha::class, 'asc');

		$sorts = $c->getSorts();
		$this->assertCount(2, $sorts);
		$this->assertSame(TimeCreated::class, $sorts[0]->class);
		$this->assertSame('desc', $sorts[0]->direction);
		$this->assertSame(Alpha::class, $sorts[1]->class);
	}

	public function testGetSortsDefaultsToEmpty(): void {
		$c = new DefaultEntityCollection();
		$this->assertSame([], $c->getSorts());
	}

	public function testGetSortOptionsReturnsExpectedClasses(): void {
		$c = new DefaultEntityCollection();
		$options = $c->getSortOptions();
		$this->assertContains(Alpha::class, $options);
		$this->assertContains(TimeCreated::class, $options);
		$this->assertContains(LastAction::class, $options);
		$this->assertContains(LikesCount::class, $options);
		$this->assertContains(MemberCount::class, $options);
		$this->assertContains(ResponsesCount::class, $options);
	}

	// --- Collection filters ---

	public function testAddFilterAccumulatesFilters(): void {
		$user = $this->createUser();
		elgg_get_session()->setLoggedInUser($user);
		try {
			$c = new DefaultEntityCollection();
			$c->addFilter(IsOwnedBy::class, $user);
			$c->addFilter(SubtypeFilter::class, null, ['subtype' => 'blog']);

			$filters = $c->getFilters();
			$this->assertCount(2, $filters);
			$this->assertSame(IsOwnedBy::class, $filters[0]->class);
			$this->assertSame(SubtypeFilter::class, $filters[1]->class);
		} finally {
			elgg_get_session()->removeLoggedInUser();
			$user->delete();
		}
	}

	public function testGetFiltersDefaultsToEmpty(): void {
		$c = new DefaultEntityCollection();
		$this->assertSame([], $c->getFilters());
	}

	// --- Collection::getList returns EntityList ---

	public function testGetListReturnsEntityList(): void {
		$c = new DefaultEntityCollection(null, ['types' => 'object']);
		$list = $c->getList();
		$this->assertInstanceOf(EntityList::class, $list);
	}

	public function testGetListRespectsPaginationParams(): void {
		$c = new DefaultEntityCollection(null, ['limit' => 7, 'offset' => 3]);
		$list = $c->getList();
		$opts = $list->getOptions();
		$this->assertSame(7, $opts->limit);
		$this->assertSame(3, $opts->offset);
	}

	// --- DefaultEntityCollection search options include CreatedBetween ---

	public function testGetSearchOptionsIncludesCreatedBetween(): void {
		$c = new DefaultEntityCollection();
		$this->assertContains(CreatedBetweenField::class, $c->getSearchOptions());
	}

	// --- EntityList validation ---

	public function testEntityListAddSortThrowsForNonSorterClass(): void {
		$list = new EntityList([]);
		$this->expectException(\InvalidArgumentException::class);
		$list->addSort(\stdClass::class);
	}

	public function testEntityListAddFilterThrowsForNonFilterClass(): void {
		$list = new EntityList([]);
		$this->expectException(\InvalidArgumentException::class);
		$list->addFilter(\stdClass::class);
	}

	// --- All filter ---

	public function testAllFilterIdReturnsAll(): void {
		$this->assertSame('all', All::id());
	}

	public function testAllFilterBuildReturnsNull(): void {
		$this->assertNull(All::build());
	}

	// --- IsOwnedBy filter ---

	public function testIsOwnedByFilterId(): void {
		$this->assertSame('is_owned_by', IsOwnedBy::id());
	}

	public function testIsOwnedByFilterBuildWithTargetReturnsWhereClause(): void {
		$user = $this->createUser();
		try {
			$clause = IsOwnedBy::build($user);
			$this->assertInstanceOf(WhereClause::class, $clause);
		} finally {
			$user->delete();
		}
	}

	public function testIsOwnedByFilterBuildWithGuidParamReturnsWhereClause(): void {
		$user = $this->createUser();
		try {
			$clause = IsOwnedBy::build(null, ['guids' => [$user->guid]]);
			$this->assertInstanceOf(WhereClause::class, $clause);
		} finally {
			$user->delete();
		}
	}

	// --- IsContainedBy filter ---

	public function testIsContainedByFilterId(): void {
		$this->assertSame('is_contained_by', IsContainedBy::id());
	}

	public function testIsContainedByFilterBuildWithNullTargetReturnsNull(): void {
		$this->assertNull(IsContainedBy::build(null));
	}

	public function testIsContainedByFilterBuildWithTargetReturnsWhereClause(): void {
		$user = $this->createUser();
		try {
			$clause = IsContainedBy::build($user);
			$this->assertInstanceOf(WhereClause::class, $clause);
		} finally {
			$user->delete();
		}
	}

	// --- SubtypeFilter ---

	public function testSubtypeFilterId(): void {
		$this->assertSame('subtype', SubtypeFilter::id());
	}

	public function testSubtypeFilterBuildWithNoSubtypeReturnsNull(): void {
		$this->assertNull(SubtypeFilter::build(null, []));
	}

	public function testSubtypeFilterBuildWithSubtypeReturnsWhereClause(): void {
		$clause = SubtypeFilter::build(null, ['subtype' => 'blog']);
		$this->assertInstanceOf(WhereClause::class, $clause);
	}

	// --- CreatedBetween filter ---

	public function testCreatedBetweenFilterId(): void {
		$this->assertSame('created_between', CreatedBetween::id());
	}

	public function testCreatedBetweenFilterBuildAlwaysReturnsWhereClause(): void {
		$clause = CreatedBetween::build(null, []);
		$this->assertInstanceOf(WhereClause::class, $clause);
	}

	public function testCreatedBetweenFilterBuildWithDatesReturnsWhereClause(): void {
		$clause = CreatedBetween::build(null, [
			'created_after' => strtotime('-7 days'),
			'created_before' => time(),
		]);
		$this->assertInstanceOf(WhereClause::class, $clause);
	}
}
