<?php

namespace hypeJunction\Data;

use Elgg\EntityNotFoundException;
use Elgg\EntityPermissionsException;
use Elgg\Http\ResponseBuilder;
use Elgg\HttpException;
use Elgg\PageNotFoundException;
use Elgg\Request;
use ElggEntity;

class DataController {

	/**
	 * Route /data
	 *
	 * @param array $segments URL segments
	 *
	 * @return ResponseBuilder
	 */
	public function __invoke(Request $request) {

		_elgg_services()->logger->disable();

		elgg_set_viewtype('json');

		// We don't want Ajax API to wrap our responses
		_elgg_services()->request->headers->remove('X-Requested-With');

		$log_level = _elgg_services()->logger->getLevel();

		elgg_set_http_header('Content-Type: application/json');

		$resource = $request->getParam('segments');

		try {
			if (!elgg_view_exists("resources/data/$resource")) {
				throw new PageNotFoundException('Unknown resource', ELGG_HTTP_NOT_IMPLEMENTED);
			}

			Page::restoreContext();

			$json = elgg_view_resource("data/$resource", $request->getParams());
			if (!$json) {
				$json = json_encode(new \stdClass());
			}

			$payload = json_decode($json, true);

			$response = [
				'status' => ELGG_HTTP_OK,
				'message' => 'OK',
				'payload' => $payload,
			];
		} catch (\Exception $ex) {
			$status = $ex->getCode() ? : ELGG_HTTP_INTERNAL_SERVER_ERROR;
			$response = [
				'status' => $status,
				'message' => $ex->getMessage(),
				'payload' => new \stdClass(),
			];

			if ($log_level) {
				$response['exception'] = $ex->getTrace();
			}
		}

		$response['system_messages'] = _elgg_services()->systemMessages->dumpRegister();

		if ($log_level) {
			$response['log'] = array_filter(_elgg_services()->logger->enable(), function ($e) use ($log_level) {
				return $e['level'] >= $log_level;
			});
		}

		return elgg_ok_response(json_encode($response));
	}

	/**
	 * Load entity from guid input
	 *
	 * @param string $type    Entity type
	 * @param string $subtype Entity subtype
	 *
	 * @return ElggEntity
	 * @throws HttpException
	 */
	public static function getEntity($type = null, $subtype = null) {
		$guid = get_input('guid');
		if (!elgg_entity_exists($guid)) {
			throw new EntityNotFoundException('Entity does not exist');
		}

		$entity = get_entity($guid);
		if (!elgg_instanceof($entity, $type, $subtype)) {
			throw new EntityPermissionsException('Entity is not accessible');
		}

		$public_subtypes = get_registered_entity_types($entity->type);
		if (!empty($public_subtypes) && !in_array($entity->getSubtype(), $public_subtypes)) {
			throw new EntityPermissionsException("\"{$entity->getSubtype()}\" is not a public subtype");
		}

		return $entity;
	}
}
