# hypeLists — Architecture (Elgg 6.x)

## Overview

hypeLists is an Elgg 6.x plugin that provides a composable query/filter/sort framework
for building paginated entity lists. It ships a data adapter API, collection primitives,
search fields, sorters, filters, and JSON/HTML rendering.

## Plugin Bootstrap

`classes/hypeJunction/Lists/Bootstrap.php` extends `Elgg\PluginBootstrap` and registers
all events during `load()`. No `start.php` or `manifest.xml`.

## Event Registrations

All hooks migrated to the Elgg 6.x event system:

| Event name | Type | Handler |
|---|---|---|
| `adapter:entity` | `all` | `Extender::addData` |
| `adapter:entity` | `all` | `Extender::addPermissions` |
| `adapter:entity` | `all` | `Extender::addCounters` |
| `adapter:entity` | `all` | `Extender::addDataLinks` |
| `adapter:entity` | `user` | `Extender::addUserData` |
| `adapter:entity` | `group` | `Extender::addGroupData` |
| `adapter:entity` | `object` | `Extender::addObjectData` |
| `get_views` | `framework:lists` | returns registered list view types |
| `search:fields` | `<collection-id>` | returns `SearchFieldInterface[]` for a collection |
| `adapter:menu_item` | `menu:<name>` | fired per menu item during export |

## Routes

Defined in `elgg-plugin.php`:

| Route pattern | Handler view |
|---|---|
| `GET /data/entity` | `resources/data/entity` |
| `GET /data/comments` | `resources/data/comments` |
| `GET /data/likes` | `resources/data/likes` |
| `GET /data/list` | `resources/data/list` |
| `GET /data/menu` | `resources/data/menu` |
| `GET /collection/owner` | `resources/collection/owner` |
| `GET /collection/friends` | `resources/collection/friends` |
| `GET /collection/group` | `resources/collection/group` |

## Key Classes

### Collection layer (`classes/hypeJunction/Lists/`)

- **`CollectionInterface`** — contract for all collection implementations
- **`Collection`** (abstract) — base implementation; manages sorts, filters, search query, render, and export
- **`DefaultEntityCollection`** — generic collection for `/collection/*` routes
- **`OwnerCollection`**, **`FriendsCollection`**, **`GroupCollection`** — context-specific collections
- **`EntityList`** — extends `Elgg\Database\Entities` with `addSort()`, `addFilter()`, `setSearchQuery()`
- **`Collections`** — named-collection registry
- **`Bootstrap`** — plugin bootstrap

### Data adapter layer (`classes/hypeJunction/Data/`)

- **`CollectionItemAdapter`** — fires `adapter:entity` event to build serializable entity array
- **`ElggMenuItemAdapter`** — fires `adapter:menu_item` event to build serializable menu item array
- **`Extender`** — default `adapter:entity` handlers (icons, tags, permissions, counters, links)
- **`DataController`** — handles `/data/*` API requests
- **`Page`** — captures and restores Elgg page context for data endpoint requests

### Search fields (`classes/hypeJunction/Lists/SearchFields/`)

- **`SearchField`** (abstract) — base; holds collection reference and request value
- **`SearchKeyword`**, **`Sort`**, **`RelationshipToViewer`**, **`CreatedBetween`**, **`Subtype`** — concrete fields

### Sorters (`classes/hypeJunction/Lists/Sorters/`)

`Alpha`, `TimeCreated`, `LastAction`, `LikesCount`, `FriendCount`, `MemberCount`, `ResponsesCount` —
each implements `SorterInterface::build($direction): WhereClause`.

### Filters (`classes/hypeJunction/Lists/Filters/`)

`IsOwnedBy`, `IsMemberOf`, `IsFriendOf`, `SubtypeFilter` — each implements
`FilterInterface::build($target, $params): WhereClause`.

## Views

- `collection/list` — paginated list wrapper
- `collection/search` — search form
- `collection/sidebar` — sidebar with stats
- `collection/widget` — widget variant
- `forms/collection/search` — search form inputs
- `resources/collection/*` — page resource views
- `resources/data/*` — JSON API resource views (under `views/json/`)
- `components/list*` — JavaScript AMD modules for async list updates

## JavaScript

AMD modules in `views/default/components/list/`:
- `list.js` — async list refresh
- `pagination.js` — pagination handling
- `init.js` — initialisation

## Elgg 5.x Migration Notes

- All `elgg_register_plugin_hook_handler()` calls replaced with `elgg_register_event_handler()`
- All `elgg_trigger_plugin_hook()` calls replaced with `elgg_trigger_event_results()`
- `\Elgg\Hook` type hints replaced with `\Elgg\Event`
- `current_page_url()` → `elgg_get_current_url()`
- `get_registered_entity_types()` → `elgg_get_registered_entity_types()`
- PHP 8.2: `FILTER_SANITIZE_STRING` → `strip_tags()`, `sizeof()` → `count()`, `array_key_first()`
- Installer name lowercased: `hypeLists` → `hypelists`
- Elgg requirement bumped: `^4.0` → `^5.0`
