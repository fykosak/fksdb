<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;
use Fykosak\Utils\Price\Price;
use Nette\Utils\DateTime;

/**
 * @property-read int $event_participant_id
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read string|null $note poznámka
 * @property-read EventParticipantStatus $status
 * @property-read DateTime $created čas vytvoření přihlášky
 * @property-read int|null $accomodation
 * @property-read string|null $diet speciální stravování
 * @property-read string|null $health_restrictions alergie, léky, úrazy
 * @property-read string|null $tshirt_size
 * @property-read string|null $tshirt_color
 * @property-read string|null $jumper_size
 * @property-read float|null $price DECIMAL(6,2) vypočtená cena
 * @property-read \DateInterval|null $arrival_time Čas příjezdu
 * @property-read string|null $arrival_destination Místo prijezdu
 * @property-read int|null $arrival_ticket společný lístek na cestu tam
 * @property-read \DateInterval|null $departure_time Čas odjezdu
 * @property-read string|null $departure_destination Místo odjezdu
 * @property-read int|null $departure_ticket společný lístek na cestu zpět
 * @property-read int|null $swimmer plavec?
 * @property-read string|null $used_drugs užívané léky
 * @property-read string|null $schedule
 * @property-read int $lunch_count
 * @phpstan-type SerializedEventParticipantModel array{
 *      participantId:int,
 *      eventId:int,
 *      code:string|null,
 *      personId:int,
 *      status:string,
 *      created:string,
 *      lunchCount:int|null,
 * }
 */
final class EventParticipantModel extends Model implements EventResource, NodeCreator
{
    public const RESOURCE_ID = 'event.participant';

    public function getPersonHistory(): ?PersonHistoryModel
    {
        return $this->person->getHistory($this->event->getContestYear());
    }

    /**
     * @throws \Exception
     */
    public function getPrice(): MultiCurrencyPrice
    {
        return new MultiCurrencyPrice([new Price(Currency::from(Currency::CZK), $this->price)]);
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @phpstan-return SerializedEventParticipantModel
     */
    public function __toArray(): array
    {
        return [
            'participantId' => $this->event_participant_id,
            'code' => $this->createMachineCode(),
            'eventId' => $this->event_id,
            'personId' => $this->person_id,
            'status' => $this->status->value,
            'created' => $this->created->format('c'),
            'lunchCount' => $this->lunch_count,
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function &__get(string $key): mixed // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'status':
                $value = EventParticipantStatus::tryFrom($value);
                break;
        }
        return $value;
    }

    public function createMachineCode(): ?string
    {
        try {
            return MachineCode::createModelHash($this->person, $this->event->getSalt());
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @throws \DOMException
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('participant');
        $node->setAttribute('eventParticipantId', (string)$this->event_participant_id);
        XMLHelper::fillArrayToNode([
            'participantId' => $this->event_participant_id,
            'eventId' => $this->event_id,
            'personId' => $this->person_id,
            'status' => $this->status->value,
            'created' => $this->created->format('c'),
            'lunchCount' => $this->lunch_count,
        ], $document, $node);
        return $node;
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<PersonScheduleModel>
     */
    public function getSchedule(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<PersonScheduleModel> $selection */
        $selection = $this->person->related(DbNames::TAB_PERSON_SCHEDULE, 'person_id')->where(
            'schedule_item.schedule_group.event_id',
            $this->event_id
        );
        return $selection;
    }
}
