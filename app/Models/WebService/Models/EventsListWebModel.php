<?php

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;

class EventsListWebModel extends WebModel {


    private ServiceEvent $serviceEvent;

    public function inject(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    /**
     * @param \stdClass $args
     * @return \SoapVar
     * @throws \SoapFault
     */
    public function getResponse(\stdClass $args): \SoapVar {
        if (!isset($args->eventTypeIds)) {
            throw new \SoapFault('Sender', 'Unknown eventType.');
        }
        $query = $this->serviceEvent->getTable()->where('event_type_id', (array)$args->eventTypeIds);
        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $rootNode = $doc->createElement('events');
        /** @var ModelEvent $event */
        foreach ($query as $event) {
            $rootNode->appendChild($event->createXMLNode($doc));
        }
        return new \SoapVar($doc->saveXML($rootNode), XSD_ANYXML);
    }
}
