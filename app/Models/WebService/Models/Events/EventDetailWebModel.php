<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Events;

use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\WebService\AESOP\Models\EventParticipantModel;
use FKSDB\Models\WebService\Models\SoapWebModel;
use FKSDB\Models\WebService\XMLHelper;
use FKSDB\Modules\CoreModule\RestApiPresenter;
use Nette\Application\BadRequestException;

/**
 * @phpstan-extends EventWebModel<array{eventId:int},array{
 *     teams?: mixed,
 *     participants?:mixed,
 *     schedule?:mixed,
 *     personSchedule?:mixed,
 * }>
 */
class EventDetailWebModel extends EventWebModel implements SoapWebModel
{
    /**
     * @throws \SoapFault
     * @throws \DOMException
     */
    public function getSOAPResponse(\stdClass $args): \SoapVar
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
    protected function getJsonResponse(): array
    {
        return $this->getEvent()->__toArray();
    }

    /**
     * @throws NotFoundException
     */
    protected function isAuthorized(): bool
    {
        return $this->eventAuthorizator->isAllowed(RestApiPresenter::RESOURCE_ID, self::class, $this->getEvent());
    }
}
