<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-import-type SerializedEventModel from EventModel
 * @phpstan-extends WebModel<array{eventTypes:array<int>},SerializedEventModel[]>
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

    protected function getJsonResponse(): array
    {
        $query = $this->eventService->getTable()->where('event_type_id', $this->params['eventTypes']);
        $events = [];
        /** @var EventModel $event */
        foreach ($query as $event) {
            if ($this->eventAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $event)) {
                $events[$event->event_id] = [
                    'eventId' => $event->event_id,
                    'year' => $event->year,
                    'eventYear' => $event->event_year,
                    'begin' => $event->begin->format('c'),
                    'end' => $event->end->format('c'),
                    'registrationBegin' => $event->registration_begin->format('c'),
                    'registrationEnd' => $event->registration_end->format('c'),
                    'report' => $event->report_cs,
                    'reportNew' => $event->report->__serialize(),
                    'description' => $event->description->__serialize(),
                    'name' => $event->name,
                    'nameNew' => $event->getName()->__serialize(),
                    'eventTypeId' => $event->event_type_id,
                    'place' => $event->place,
                    'contestId' => $event->event_type->contest_id,
                ];
            }
        }
        return $events;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'eventTypes' => Expect::listOf(Expect::scalar()->castTo('int')),
        ]);
    }

    protected function isAuthorized(): bool
    {
        return $this->contestAuthorizator->isAllowedAnyContest(RestApiPresenter::RESOURCE_ID, self::class);
    }
}
