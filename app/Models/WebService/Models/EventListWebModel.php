<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array<string,mixed>,array<string,mixed>>
 */
class EventListWebModel extends WebModel
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
    public function getResponse(\stdClass $args): \SoapVar
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

    public function getJsonResponse(array $params): array
    {
        $query = $this->eventService->getTable()->where('event_type_id', $params['event_type_ids']);
        $events = [];
        /** @var EventModel $event */
        foreach ($query as $event) {
            $events[$event->event_id] = $event->__toArray();
        }
        return $events;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_type_ids' => Expect::listOf(Expect::scalar()->castTo('int'))->required(),
        ]);
    }
}
