<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class EventListWebModel extends WebModel
{

    private ServiceEvent $serviceEvent;

    public function inject(ServiceEvent $serviceEvent): void
    {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->eventTypeIds)) {
            throw new \SoapFault('Sender', 'Unknown eventType.');
        }
        $query = $this->serviceEvent->getTable()->where('event_type_id', (array)$args->eventTypeIds);
        $document = new \DOMDocument();
        $document->formatOutput = true;
        $rootNode = $document->createElement('events');
        /** @var ModelEvent $event */
        foreach ($query as $event) {
            $rootNode->appendChild($event->createXMLNode($document));
        }
        return new \SoapVar($document->saveXML($rootNode), XSD_ANYXML);
    }

    public function getJsonResponse(array $params): array
    {
        $query = $this->serviceEvent->getTable()->where('event_type_id', $params['event_type_ids']);
        $events = [];
        /** @var ModelEvent $event */
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
