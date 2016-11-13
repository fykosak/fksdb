<?php

namespace Github;

use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventFactory extends Object {

	const HTTP_HEADER = 'X-GitHub-Event';

	private static $typeMap = array(
		'ping' => 'createPing',
		'push' => 'createPush',
	);

	private $repositoryCache = array();

	public function createEvent($type, $data) {
		if (!array_key_exists($type, self::$typeMap)) {
			throw new UnsupportedEventException('Unsupported event type.'); // is it XSS safe print the type?
		}
		$method = self::$typeMap[$type];
		return $this->$method($data);
	}

	private function createPing($data) {
		$event = new Events\PingEvent();
		$this->fillBase($event, $data);
		self::fillHelper(array('zen', 'hook_id'), $event, $data);
		return $event;
	}

	private function createPush($data) {
		$event = new Events\PushEvent();
		$this->fillBase($event, $data);
		self::fillHelper(array('before', 'after', 'ref'), $event, $data);
		return $event;
	}

	private function createRepository($data) {
		if (!array_key_exists('id', $data)) {
			throw new MissingEventFieldException('id');
		}

		$id = $data['id'];
		if (!array_key_exists($id, $this->repositoryCache)) {
			$repository = new Repository();
			$repository->id = $id;
			/*
			 * Store repo in cache so that we can resolve cyclic dependencies 
			 * between owner/user 
			 */
			$this->repositoryCache[$id] = $repository;

			if (!array_key_exists('full_name', $data)) {
				throw new MissingEventFieldException('full_name');
			}

			if (!array_key_exists('owner', $data)) {
				throw new MissingEventFieldException('owner');
			}

			$repository->full_name = $data['full_name'];
			$repository->owner = $this->createUser($data['owner']);
		}

		return $this->repositoryCache[$id];
	}

	private function createUser($data) {
		/* Github API is underspecified mess regarding users/their
		 * attributes so just create a new User instance every time and fill it with
 		 * whatever we can store.
		*/
		$user = new User();
		self::fillHelper(array('login', 'id'), $user, $data, false);

		return $user;
	}

	private function fillBase($event, $data) {
		if (!array_key_exists('repository', $data)) {
			throw new MissingEventFieldException('repository');
		}
		$event->repository = $this->createRepository($data['repository']);

		if (!array_key_exists('sender', $data)) {
			throw new MissingEventFieldException('sender');
		}
		$event->sender = $this->createUser($data['sender']);
	}

	private static function fillHelper($definition, $object, $data, $strict = false) {
		foreach ($definition as $key) {
			if (!array_key_exists($key, $data)) {
				if ($strict) {
					throw new MissingEventFieldException($key);
				} else {
					continue;
				}
			}
			$object->$key = $data[$key];
		}
	}

}

class UnsupportedEventException extends InvalidArgumentException {
	
}

class MissingEventFieldException extends InvalidArgumentException {
	
}
