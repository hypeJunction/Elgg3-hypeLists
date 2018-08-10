<?php

namespace hypeJunction\Data;

use ElggEntity;
use hypeJunction\Post\Model;

class Extender {

	/**
	 * Add entity specific data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity ElggEntity */

		if (!$entity instanceof ElggEntity) {
			return;
		}

		$type = $entity->type;
		$subtype = $entity->getSubtype();

		if (elgg_is_admin_logged_in()) {
			$return['metadata'] = $entity->getAllMetadata();
		}

		$return['display_name'] = $entity->getDisplayName();
		$return['time_created'] = $entity->time_created;
		$return['time_updated'] = $entity->time_updated;

		$return['location'] = $entity->getLocation();
		$return['latitude'] = $entity->getLatitude();
		$return['longitude'] = $entity->getLongitude();

		$return['status'] = $entity->status;

		foreach (['excerpt', 'briefdescription', 'description'] as $prop) {
			if ($entity->$prop) {
				$return['summary'] = elgg_get_excerpt($entity->$prop);
				break;
			}
		}

		$return['_links']['icons'] = [];
		$return['_links']['cover'] = [];

		$icon_sizes = array_keys((array) elgg_get_icon_sizes($type, $subtype));

		foreach ($icon_sizes as $icon_size) {
			$return['_links']['icons'][$icon_size] = $entity->getIconURL([
				'size' => $icon_size,
				'use_cookie' => false,
			]);
		}

		$cover_sizes = array_keys((array) elgg_get_icon_sizes($type, $subtype, 'cover'));

		foreach ($cover_sizes as $cover_size) {
			$return['_links']['cover'][$cover_size] = $entity->getIconURL([
				'size' => $cover_size,
				'type' => 'cover',
				'use_cookie' => false,
			]);
		}

		$tag_names = elgg_get_registered_tag_metadata_names();
		foreach ($tag_names as $tag_name) {
			$return[$tag_name] = [];
			foreach ((array) $entity->$tag_name as $tag) {
				$return[$tag_name][] = [
					'label' => $tag,
					'url' => elgg_http_add_url_query_elements(elgg_normalize_url('search'), [
						'q' => $tag,
						'search_type' => 'tags',
					]),
				];
			}
		}

		return $return;
	}

	/**
	 * Add permissions
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addPermissions($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity ElggEntity */

		if (!$entity instanceof ElggEntity) {
			return;
		}

		$return['_permissions']['edit'] = $entity->canEdit();
		$return['_permissions']['comment'] = $entity->canComment();

		$registered = get_registered_entity_types();

		foreach ($registered as $type => $subtypes) {
			if ($subtypes) {
				foreach ($subtypes as $subtype) {
					$return['_permissions']['write'][$type][$subtype] = $entity->canWriteToContainer(0, $type, $subtype);
				}
			} else {
				$return['_permissions']['write'][$type] = $entity->canWriteToContainer(0, $type);
			}
		}

		$annotations = [
			'likes',
		];

		foreach ($annotations as $annotation) {
			$return['_permissions']['annotate'][$annotation] = $entity->canAnnotate(0, $annotation);
		}

		return $return;
	}

	/**
	 * Add user specific data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "user"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addUserData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggUser */

		if (!$entity instanceof \ElggUser) {
			return;
		}

		$fields = (array) elgg_get_config('profile_fields');
		foreach ($fields as $field => $field_type) {
			if (isset($return[$field])) {
				continue;
			}

			$return[$field] = $entity->$field;
		}

		$return['_counters']['friends'] = elgg_get_total_friends($entity);
		$return['_links']['friends'] = elgg_http_add_url_query_elements("user/friends", [
			'guid' => $entity->guid,
		]);
		$return['_links']['friends_of'] = elgg_http_add_url_query_elements("user/friends_of", [
			'guid' => $entity->guid,
		]);

		$user_guid = elgg_get_logged_in_user_guid();
		$return['_relationships']['friend'] = $entity->isFriendsWith($user_guid);
		$return['_relationships']['friend_of'] = $entity->isFriendOf($user_guid);

		return $return;
	}

	/**
	 * Add group specific data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "group"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addGroupData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggGroup */

		if (!$entity instanceof \ElggGroup) {
			return;
		}

		$fields = (array) elgg_get_config('group');
		foreach ($fields as $field => $field_type) {
			if (isset($return[$field])) {
				continue;
			}

			$return[$field] = $entity->$field;
		}

		$return['access'] = self::getAccessData($entity);
		$return['access']['content_access_mode'] = $entity->getContentAccessMode();
		$return['access']['membership'] = $entity->membership;
		$return['access']['group_acl'] = $entity->group_acl;

		$return['_counters']['members'] = elgg_get_total_members($entity);
		$return['_links']['members'] = elgg_http_add_url_query_elements("group/members", [
			'guid' => $entity->guid,
		]);

		$user = elgg_get_logged_in_user_entity();
		$return['_relationships']['member'] = $entity->isMember($user);

		return $return;
	}

	/**
	 * Add object data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "object"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addObjectData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggObject */

		if (!$entity instanceof \ElggObject) {
			return;
		}

		$return['access'] = self::getAccessData($entity);

		if ($entity instanceof \ElggFile) {
			$return['_links']['download'] = elgg_get_download_url($entity);
			$return['_links']['inline'] = elgg_get_inline_url($entity);

			$return['media'] = [
				'display' => 'image',
				'src' => $entity->getIconURL('large'),
			];
		}

		return $return;
	}

	/**
	 * Add counters
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addCounters($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity ElggEntity */

		if (!$entity instanceof ElggEntity) {
			return;
		}

		$return['_counters']['comments'] = elgg_get_total_comments($entity);
		$return['_counters']['likes'] = elgg_get_total_likes($entity);

		return $return;
	}

	/**
	 * Add data links to entity export
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addDataLinks($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);

		if (!$entity instanceof ElggEntity) {
			return;
		}

		$type = $entity->type;
		$subtype = $entity->getSubtype();

		if ($entity->owner_guid) {
			$return['_links']['owner'] = elgg_http_add_url_query_elements("data/entity", [
				'guid' => $entity->owner_guid,
			]);
		} else {
			$return['_links']['owner'] = false;
		}

		if ($entity->container_guid) {
			$return['_links']['container'] = elgg_http_add_url_query_elements("data/entity", [
				'guid' => $entity->container_guid,
			]);
		} else {
			$return['_links']['container'] = false;
		}

		$return['_links']['comments'] = elgg_http_add_url_query_elements("data/comments", [
			'guid' => $entity->guid,
		]);

		if (elgg_is_active_plugin('likes')) {
			$likable = (bool) elgg_trigger_plugin_hook('likes:is_likable', "$type:$subtype", [], false);
			if ($likable) {
				$return['_links']['likes'] = elgg_http_add_url_query_elements("data/likes", [
					'guid' => $entity->guid,
				]);
			} else {
				$return['_links']['likes'] = false;
			}
		}

		return $return;
	}

	/**
	 * Returns entity access info
	 *
	 * @param ElggEntity $entity
	 *
	 * @return array
	 */
	public static function getAccessData(ElggEntity $entity) {

		$access = $entity->access_id;
		$icon = function () use ($access) {
			switch ($access) {
				case ACCESS_FRIENDS :
					$icon_name = 'user';
					break;
				case ACCESS_PUBLIC :
				case ACCESS_LOGGED_IN :
					$icon_name = 'globe';
					break;
				case ACCESS_PRIVATE :
					$icon_name = 'lock';
					break;
				default:
					$icon_name = 'cog';
					break;
			}

			return $icon_name;
		};

		return [
			'id' => $entity->access_id,
			'icon' => $icon(),
			'label' => get_readable_access_level($entity->access_id),
		];
	}
}
