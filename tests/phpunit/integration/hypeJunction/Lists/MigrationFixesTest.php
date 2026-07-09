<?php

namespace hypeJunction\Lists;

use Elgg\IntegrationTestCase;
use hypeJunction\Data\Extender;

/**
 * Regression guard for the Elgg 7.x migration fixes applied to hypelists.
 *
 * One test per landed fix (git ref in the docblock). Each asserts the FIXED
 * shape so a future edit that reintroduces the pre-migration form fails here,
 * not silently at runtime on a 7.x list page.
 */
class MigrationFixesTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypelists';
	}

	public function up() {}
	public function down() {}

	/** Resolve the plugin root by walking up to elgg-plugin.php. */
	private function pluginRoot(): string {
		$dir = __DIR__;
		for ($i = 0; $i < 6; $i++) {
			if (is_file($dir . '/elgg-plugin.php')) {
				return $dir;
			}
			$dir = dirname($dir);
		}
		$this->fail('Could not locate plugin root (elgg-plugin.php) above ' . __DIR__);
	}

	/**
	 * aa733cb — the list/search ESM modules must default-import elgg/i18n and
	 * wrap echo locally. A named `import { echo } from 'elgg/i18n'` throws
	 * "no export named echo" and aborts the whole list bundle on Elgg 7.
	 */
	public function testListEsmModulesUseDefaultI18nImport() {
		$root = $this->pluginRoot();
		$modules = [
			'views/default/js/components/list/defaults.mjs',
			'views/default/js/components/list/list.mjs',
			'views/default/js/components/list/pagination.mjs',
			'views/default/js/forms/collection/search.mjs',
		];

		foreach ($modules as $rel) {
			$path = "$root/$rel";
			$this->assertFileExists($path);
			$src = file_get_contents($path);

			$this->assertStringContainsString(
				"import i18n from 'elgg/i18n'",
				$src,
				"$rel must default-import i18n"
			);
			$this->assertDoesNotMatchRegularExpression(
				"/import\s*\{\s*echo\b/",
				$src,
				"$rel must not named-import { echo } from elgg/i18n (throws on Elgg 7)"
			);
		}
	}

	/**
	 * 0b04f94 — the six ESM-shaped modules must carry the .mjs extension (so
	 * Elgg registers them in the importmap) and the orphaned AMD
	 * components/list*.js residue must be gone.
	 */
	public function testEsmModulesUseMjsExtensionAndNoAmdResidue() {
		$root = $this->pluginRoot();
		$mjs = [
			'views/default/js/components/list.mjs',
			'views/default/js/components/list/init.mjs',
			'views/default/js/components/list/list.mjs',
			'views/default/js/components/list/pagination.mjs',
			'views/default/js/components/list/defaults.mjs',
			'views/default/js/forms/collection/search.mjs',
		];
		foreach ($mjs as $rel) {
			$this->assertFileExists("$root/$rel", "$rel must exist as an .mjs ES module");
		}

		$amd = array_merge(
			glob("$root/views/default/js/components/list.js") ?: [],
			glob("$root/views/default/js/components/list/*.js") ?: []
		);
		$this->assertSame([], $amd, 'Orphaned AMD components/list*.js residue must be deleted: ' . implode(', ', $amd));
	}

	/**
	 * 335ffba — elgg-plugin.php must require lib/functions.php at the top so the
	 * git-tracked global helpers exist, and those helpers must be defined once
	 * the plugin is booted.
	 */
	public function testElggPluginRequiresGlobalHelperLibAndHelpersExist() {
		$root = $this->pluginRoot();
		$manifest = file_get_contents("$root/elgg-plugin.php");
		$this->assertMatchesRegularExpression(
			"#require_once\s+__DIR__\s*\.\s*'/lib/functions\.php'#",
			$manifest,
			'elgg-plugin.php must require_once lib/functions.php'
		);

		$this->assertTrue(function_exists('elgg_register_collection'));
		$this->assertTrue(function_exists('elgg_get_collection'));
		$this->assertTrue(function_exists('elgg_view_collection'));
	}

	/**
	 * 1a31669 — the group collection resource must resolve the group through
	 * the gatekeeper (null-guarded get_entity) and 404 on an unbuildable
	 * collection, rather than fatally dereferencing a null entity on 7.x.
	 */
	public function testCollectionGroupResourceGuardsMissingEntity() {
		$root = $this->pluginRoot();
		$src = file_get_contents("$root/views/default/resources/collection/group.php");

		$this->assertStringContainsString('elgg_entity_gatekeeper(', $src,
			'group resource must resolve the entity via elgg_entity_gatekeeper (null-guarded)');
		$this->assertStringContainsString('PageNotFoundException', $src,
			'group resource must 404 when the collection cannot be built');
	}

	/**
	 * d9acb9a — Extender::addPermissions must build the write matrix from the
	 * searchable-capability registry; the removed elgg_get_registered_entity_types()
	 * must not reappear.
	 */
	public function testExtenderAddPermissionsUsesCapabilityRegistry() {
		$src = file_get_contents($this->pluginRoot() . '/classes/hypeJunction/Data/Extender.php');

		$this->assertStringContainsString("elgg_entity_types_with_capability('searchable')", $src);
		$this->assertStringNotContainsString('elgg_get_registered_entity_types(', $src,
			'elgg_get_registered_entity_types() was removed in 7.x');
	}

	/**
	 * 294a3ce — Extender::addCounters must read comment/like counts off the
	 * entity, not via the removed elgg_get_total_comments/elgg_get_total_likes.
	 */
	public function testExtenderAddCountersUsesEntityCountMethods() {
		$src = file_get_contents($this->pluginRoot() . '/classes/hypeJunction/Data/Extender.php');

		$this->assertStringContainsString('->countComments()', $src);
		$this->assertStringContainsString("->countAnnotations('likes')", $src);
		$this->assertStringNotContainsString('elgg_get_total_comments(', $src);
		$this->assertStringNotContainsString('elgg_get_total_likes(', $src);
	}

	/**
	 * b8e6699 — the adapter:entity handlers were migrated to the \Elgg\Event
	 * signature. Assert the actual method parameter type, not just the source.
	 */
	public function testAdapterHandlersUseElggEventSignature() {
		foreach (['addData', 'addPermissions', 'addCounters', 'addDataLinks', 'addUserData', 'addGroupData', 'addObjectData'] as $method) {
			$ref = new \ReflectionMethod(Extender::class, $method);
			$params = $ref->getParameters();
			$this->assertNotEmpty($params, "Extender::$method must take an event argument");
			$type = $params[0]->getType();
			$this->assertInstanceOf(\ReflectionNamedType::class, $type);
			$this->assertSame(\Elgg\Event::class, ltrim($type->getName(), '\\'),
				"Extender::$method must type-hint \\Elgg\\Event");
		}
	}

	/**
	 * 37a85b6 — the owner/friends resources (both default and json viewtypes)
	 * must resolve the target user via elgg_get_user_by_username; the 5.x-removed
	 * get_user_by_username() must be gone.
	 */
	public function testOwnerAndFriendsResourcesResolveUserViaElggGetUserByUsername() {
		$root = $this->pluginRoot();
		$resources = [
			'views/default/resources/collection/owner.php',
			'views/default/resources/collection/friends.php',
			'views/json/resources/collection/owner.php',
			'views/json/resources/collection/friends.php',
		];
		foreach ($resources as $rel) {
			$src = file_get_contents("$root/$rel");
			$this->assertStringContainsString('elgg_get_user_by_username(', $src, "$rel must resolve via elgg_get_user_by_username");
			$this->assertDoesNotMatchRegularExpression('/(?<![\w_])get_user_by_username\s*\(/', $src,
				"$rel must not call the removed get_user_by_username()");
		}
	}

	/**
	 * c045f5c — composer autoload must be psr-4 (hypeJunction\ =>
	 * classes/hypeJunction/); psr-0 silently fails under the Elgg 7 plugin
	 * autoloader.
	 */
	public function testComposerAutoloadIsPsr4() {
		$composer = json_decode(file_get_contents($this->pluginRoot() . '/composer.json'), true);

		$this->assertArrayHasKey('psr-4', $composer['autoload']);
		$this->assertArrayNotHasKey('psr-0', $composer['autoload']);
		$this->assertSame('classes/hypeJunction/', $composer['autoload']['psr-4']['hypeJunction\\']);

		// prove the mapping actually resolves core classes on a booted 7.x
		$this->assertTrue(class_exists(Collections::class));
		$this->assertTrue(class_exists(Extender::class));
	}

	/**
	 * Key behavior: the default collection registered by Bootstrap::init exposes
	 * exactly the seven sorter classes and appends CreatedBetween to the base
	 * search options, and an unknown collection name resolves to null.
	 */
	public function testDefaultCollectionBuilderContract() {
		$collection = elgg_get_collection('collection:default');
		$this->assertInstanceOf(DefaultEntityCollection::class, $collection);

		$this->assertSame([
			\hypeJunction\Lists\Sorters\Alpha::class,
			\hypeJunction\Lists\Sorters\TimeCreated::class,
			\hypeJunction\Lists\Sorters\LastAction::class,
			\hypeJunction\Lists\Sorters\LikesCount::class,
			\hypeJunction\Lists\Sorters\FriendCount::class,
			\hypeJunction\Lists\Sorters\MemberCount::class,
			\hypeJunction\Lists\Sorters\ResponsesCount::class,
		], $collection->getSortOptions());

		$search = $collection->getSearchOptions();
		$this->assertContains(\hypeJunction\Lists\SearchFields\CreatedBetween::class, $search);
		$this->assertSame(\hypeJunction\Lists\SearchFields\CreatedBetween::class, end($search),
			'CreatedBetween must be appended after the base search options');

		$this->assertNull(elgg_get_collection('collection:does-not-exist'),
			'unknown collection name must resolve to null');
	}
}
