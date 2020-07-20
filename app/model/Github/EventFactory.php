<?php

namespace Github;

use Github\Events\Event;
use Github\Events\PingEvent;
use Github\Events\PushEvent;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class EventFactory {
    use SmartObject;

    const HTTP_HEADER = 'X-GitHub-Event';

    /** @var Repository[] */
    private $repositoryCache = [];

    public function createEvent(string $type, array $data): Event {
        switch ($type) {
            case 'ping':
                return $this->createPing($data);
            case 'push':
                return $this->createPush($data);
        }
        throw new UnsupportedEventException('Unsupported event type.'); // is it XSS safe print the type?
    }

    private function createPing(array $data): PingEvent {
        $event = new Events\PingEvent();
        $this->fillBase($event, $data);
        self::fillHelper(['zen', 'hook_id'], $event, $data);
        return $event;
    }

    private function createPush(array $data): PushEvent {
        $event = new Events\PushEvent();
        $this->fillBase($event, $data);
        self::fillHelper(['before', 'after', 'ref'], $event, $data);
        return $event;
    }

    private function createRepository(array $data): Repository {
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

    /**
     * @param mixed $data
     * @return User
     */
    private function createUser(array $data): User {
        /* Github API is underspecified mess regarding users/their
         * attributes so just create a new User instance every time and fill it with
          * whatever we can store.
        */
        $user = new User();
        self::fillHelper(['login', 'id'], $user, $data, false);

        return $user;
    }

    /**
     * @param Event $event
     * @param array $data
     * @return void
     */
    private function fillBase(Event $event, array $data) {
        if (!array_key_exists('repository', $data)) {
            throw new MissingEventFieldException('repository');
        }
        $event->repository = $this->createRepository($data['repository']);

        if (!array_key_exists('sender', $data)) {
            throw new MissingEventFieldException('sender');
        }
        $event->sender = $this->createUser($data['sender']);
    }

    /**
     * @param array $definition
     * @param object $object
     * @param array $data
     * @param bool $strict
     * @return void
     */
    private static function fillHelper(array $definition, $object, array $data, bool $strict = false) {
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

/**
 * Class UnsupportedEventException
 * *
 */
class UnsupportedEventException extends InvalidArgumentException {

}

/**
 * Class MissingEventFieldException
 * *
 */
class MissingEventFieldException extends InvalidArgumentException {

}
