<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Security\Resource;

/**
 * @property-read int $schedule_group_id
 * @property-read ScheduleGroupType $schedule_group_type
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read \DateTimeInterface $start
 * @property-read \DateTimeInterface $end
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read LocalizedString $name
 * @property-read \DateTimeInterface|null $registration_begin
 * @property-read \DateTimeInterface|null $registration_end
 * @property-read \DateTimeInterface|null $modification_end
 * @phpstan-type SerializedScheduleGroupModel array{
 *      scheduleGroupId:int,
 *      scheduleGroupType:string,
 *      registrationBegin:\DateTimeInterface|null,
 *      registrationEnd:\DateTimeInterface|null,
 *      modificationEnd:\DateTimeInterface|null,
 *      name:array<string, string>,
 *      eventId:int,
 *      start:string,
 *      end:string,
 * }
 */
final class ScheduleGroupModel extends Model implements Resource, NodeCreator
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
            'registrationBegin' => $this->getRegistrationBegin(),
            'registrationEnd' => $this->getRegistrationEnd(),
            'modificationEnd' => $this->getModificationEnd(),
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

    public function canCreate(): bool
    {
        $begin = $this->getRegistrationBegin();
        $end = $this->getRegistrationEnd();
        return ($begin && $begin->getTimestamp() <= time()) && ($end && $end->getTimestamp() >= time());
    }

    public function canEdit(): bool
    {
        $begin = $this->getRegistrationBegin();
        $end = $this->getModificationEnd();
        return ($begin && $begin->getTimestamp() <= time()) && ($end && $end->getTimestamp() >= time());
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

    public function getRegistrationBegin(): ?\DateTimeInterface
    {
        return $this->registration_begin ?? $this->event->registration_begin;
    }

    public function getRegistrationEnd(): ?\DateTimeInterface
    {
        return $this->registration_end ?? $this->event->registration_end;
    }

    public function getModificationEnd(): ?\DateTimeInterface
    {
        return $this->modification_end ?? $this->registration_end ?? $this->event->registration_end;
    }

    public function hasStarted(): bool
    {
        return $this->start->getTimestamp() < time();
    }

    public function hasEnded(): bool
    {
        return $this->end->getTimestamp() < time();
    }

    /**
     * @return ScheduleGroupType|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        switch ($key) {
            case 'schedule_group_type':
                $value = ScheduleGroupType::tryFrom(parent::__get($key));
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
}
