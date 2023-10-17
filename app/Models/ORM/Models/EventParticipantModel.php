<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;
use Fykosak\Utils\Price\Price;
use Nette\Security\Resource;

/**
 * @property-read int $event_participant_id
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read int $person_id
 * @property-read PersonModel $person
 * @property-read string|null $note poznámka
 * @property-read EventParticipantStatus $status
 * @property-read \DateTimeInterface $created čas vytvoření přihlášky
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
final class EventParticipantModel extends Model implements Resource, NodeCreator
{

    public const RESOURCE_ID = 'event.participant';

    public function getPersonHistory(): ?PersonHistoryModel
    {
        return $this->person->getHistoryByContestYear($this->event->getContestYear());
    }

    public function __toString(): string
    {
        return $this->person->getFullName();
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
     * @return EventParticipantStatus|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
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
            return MachineCode::createHash($this, MachineCode::getSaltForEvent($this->event));
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
        XMLHelper::fillArrayToNode($this->__toArray(), $document, $node);
        return $node;
    }
}
