<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\Authorization\Resource\EventResource;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Utils\DateTime;

/**
 * @property-read int $schedule_group_id
 * @property-read ScheduleGroupType $schedule_group_type
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read DateTime $start
 * @property-read DateTime $end
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read LocalizedString $name
 * @property-read DateTime|null $registration_begin
 * @property-read DateTime|null $registration_end
 * @phpstan-type SerializedScheduleGroupModel array{
 *      scheduleGroupId:int,
 *      scheduleGroupType:string,
 *      registrationBegin:string|null,
 *      registrationEnd:string|null,
 *      name:array<string, string>,
 *      eventId:int,
 *      start:string,
 *      end:string,
 * }
 */
final class ScheduleGroupModel extends Model implements EventResource, NodeCreator
{
    public const RESOURCE_ID = 'event.schedule.group';

    /**
     * @phpstan-return TypedGroupedSelection<ScheduleItemModel>
     */
    public function getItems(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<ScheduleItemModel> $selection */
        $selection = $this->related(DbNames::TAB_SCHEDULE_ITEM);
        return $selection;
    }

    /**
     * @phpstan-return SerializedScheduleGroupModel
     */
    public function __toArray(): array
    {
        return [
            'scheduleGroupId' => $this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type->value,
            'registrationBegin' => $this->registration_begin ? $this->registration_begin->format('c') : null,
            'registrationEnd' => $this->registration_end ? $this->registration_end->format('c') : null,
            'name' => $this->name->__serialize(),
            'eventId' => $this->event_id,
            'start' => $this->start->format('c'),
            'end' => $this->end->format('c'),
        ];
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function isModifiable(): bool
    {
        $begin = $this->registration_begin ?? $this->event->registration_begin;
        $end = $this->registration_end ?? $this->event->registration_end;
        return ($begin->getTimestamp() <= time()) && ($end->getTimestamp() >= time());
    }

    public function hasFreeCapacity(): bool
    {
        $available = 0;
        try {
            /** @var ScheduleItemModel $item */
            foreach ($this->getItems() as $item) {
                $available += $item->getAvailableCapacity();
            }
        } catch (\LogicException $exception) {
            return true;
        }

        return $available > 0;
    }

    /**
     * @return ScheduleGroupType|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        switch ($key) {
            case 'schedule_group_type':
                $value = ScheduleGroupType::from(parent::__get($key));
                break;
            case 'name':
                $value = new LocalizedString([
                    'cs' => $this->name_cs,
                    'en' => $this->name_en,
                ]);
                break;
            default:
                $value = parent::__get($key);
        }
        return $value;
    }

    /**
     * @throws \DOMException
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('scheduleGroup');
        $node->setAttribute('scheduleGroupId', (string)$this->schedule_group_id);
        XMLHelper::fillArrayToNode([
            'scheduleGroupId' => (string)$this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type->value,
            'eventId' => (string)$this->event_id,
            'start' => $this->start->format('c'),
            'end' => $this->end->format('c'),
        ], $document, $node);
        XMLHelper::fillArrayArgumentsToNode('lang', [
            'name' => $this->name->__serialize(),
        ], $document, $node);
        return $node;
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }
}
