<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\SoapWebModel;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedEventModel from EventModel
 * @phpstan-extends WebModel<array{eventTypes:array<int>},SerializedEventModel[]>
 */
class EventListWebModel extends WebModel implements SoapWebModel
{
    private EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getSOAPResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->eventTypeIds)) {
            throw new \SoapFault('Sender', 'Unknown eventType.');
        }
        $query = $this->eventService->getTable()->where('event_type_id', (array)$args->eventTypeIds);
        $document = new \DOMDocument();
        $document->formatOutput = true;
        $rootNode = $document->createElement('events');
        /** @var EventModel $event */
        foreach ($query as $event) {
            $rootNode->appendChild($event->createXMLNode($document));
        }
        return new \SoapVar($document->saveXML($rootNode), XSD_ANYXML);
    }

    protected function getJsonResponse(): array
    {
        $query = $this->eventService->getTable()->where('event_type_id', $this->params['eventTypes']);
        $events = [];
        /** @var EventModel $event */
        foreach ($query as $event) {
            if (
                $this->authorizator->isAllowedEvent(
                    EventResourceHolder::fromResourceId(RestApiPresenter::RESOURCE_ID, $event),
                    self::class,
                    $event
                )
            ) {
                $events[$event->event_id] = $event->__toArray();
            }
        }
        return $events;
    }

    protected function getExpectedParams(): array
    {
        return [
            'eventTypes' => Expect::listOf(Expect::scalar()->castTo('int')),
        ];
    }

    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(RestApiPresenter::RESOURCE_ID, self::class);
    }
}
