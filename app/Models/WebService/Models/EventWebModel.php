<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\WebService\XMLHelper;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array{event_id?:int,eventId:int},array{
 *     teams?: mixed,
 *     participants?:mixed,
 *     schedule?:mixed,
 *     personSchedule?:mixed,
 * }>
 */
class EventWebModel extends WebModel
{
    private EventService $eventService;
    private PersonScheduleService $personScheduleService;

    public function inject(EventService $eventService, PersonScheduleService $personScheduleService): void
    {
        $this->eventService = $eventService;
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getResponse(\stdClass $args): \SoapVar
    {
        if (!isset($args->eventId)) {
            throw new \SoapFault('Sender', 'Unknown eventId.');
        }
        $event = $this->eventService->findByPrimary($args->eventId);
        if (is_null($event)) {
            throw new \SoapFault('Sender', 'Unknown event.');
        }
        $doc = new \DOMDocument();
        $root = $this->createEventDetailNode($doc, $event);
        $root->appendChild($this->createParticipantListNode($doc, $event));
        $doc->formatOutput = true;
        return new \SoapVar($doc->saveXML($root), XSD_ANYXML);
    }

    /**
     * @param EventModel $event
     * @return array<array{person:mixed,scheduleItemId:int}>
     */
    private function createPersonScheduleArray(EventModel $event): array
    {
        $data = [];
        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $event->event_id);
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            $data[] = [
                'person' => $model->person->__toArray(),
                'scheduleItemId' => $model->schedule_item_id,
            ];
        }
        return $data;
    }

    /**
     * @throws \Exception
     * @phpstan-import-type SerializedScheduleGroupModel from ScheduleGroupModel
     * @phpstan-import-type SerializedScheduleItemModel from ScheduleItemModel
     * @phpstan-return (SerializedScheduleGroupModel&array{
     *     scheduleItems:SerializedScheduleItemModel[],
     *     schedule_items:SerializedScheduleItemModel[],
     *  })[]
     * @phpstan-ignore-next-line
     */
    private function createScheduleListArray(EventModel $event): array
    {
        $data = [];
        /** @var ScheduleGroupModel $group */
        foreach ($event->getScheduleGroups() as $group) {
            $datum = $group->__toArray();
            $datum['schedule_items'] = [];
            $datum['scheduleItems'] = [];
            /** @var ScheduleItemModel $item */
            foreach ($group->getItems() as $item) {
                $datum['schedule_items'][] = $item->__toArray();
                $datum['scheduleItems'][] = $item->__toArray();
            }
            $data[] = $datum;
        }
        return $data;
    }

    /**
     * @throws \DOMException
     */
    private function createEventDetailNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('eventDetail');
        $rootNode->appendChild($event->createXMLNode($doc));
        return $rootNode;
    }

    /**
     * @throws \DOMException
     */
    private function createParticipantListNode(\DOMDocument $doc, EventModel $event): \DOMElement
    {
        $rootNode = $doc->createElement('participants');
        /** @var EventParticipantModel $participant */
        foreach ($event->getParticipants() as $participant) {
            $pNode = $this->createParticipantNode($participant, $doc);
            $rootNode->appendChild($pNode);
        }
        return $rootNode;
    }

    /**
     * @throws \DOMException
     */
    private function createParticipantNode(EventParticipantModel $participant, \DOMDocument $doc): \DOMElement
    {
        $pNode = $participant->createXMLNode($doc);
        $history = $participant->getPersonHistory();
        XMLHelper::fillArrayToNode([
            'name' => $participant->person->getFullName(),
            'personId' => (string)$participant->person->person_id,
            'email' => $participant->person->getInfo()->email,
            'schoolId' => $history ? (string)$history->school_id : null,
            'schoolName' => $history ? $history->school->name_abbrev : null,
            'studyYear' => $history ? (string)$history->study_year_new->numeric() : null,
            'studyYearNew' => $history ? $history->study_year_new->value : null,
            'countryIso' => $history ? (
            ($school = $history->school) ? $school->address->country->alpha_2 : null) : null,
        ], $doc, $pNode);
        return $pNode;
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function getJsonResponse(array $params): array
    {
        $event = $this->eventService->findByPrimary($params['event_id'] ?? $params['eventId']);
        if (is_null($event)) {
            throw new BadRequestException('Unknown event.', IResponse::S404_NOT_FOUND);
        }
        $data = $event->__toArray();
        $data['schedule'] = $this->createScheduleListArray($event);
        $data['personSchedule'] = $this->createPersonScheduleArray($event);
        return $data;
    }

    protected function getExpectedParams(): Structure
    {
        return Expect::structure([
            'event_id' => Expect::scalar()->castTo('int'),
            'eventId' => Expect::scalar()->castTo('int'),
        ]);
    }

    protected function isAuthorized(array $params): bool
    {
        $event = $this->eventService->findByPrimary($params['eventId']);
        if (!$event) {
            return false;
        }
        return $this->eventAuthorizator->isAllowed($event, 'api', $event);
    }
}
