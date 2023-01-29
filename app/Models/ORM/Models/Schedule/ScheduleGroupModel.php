<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int schedule_group_id
 * @property-read ScheduleGroupType schedule_group_type
 * @property-read int event_id
 * @property-read EventModel event
 * @property-read \DateTimeInterface start
 * @property-read \DateTimeInterface end
 * @property-read string name_cs
 * @property-read string name_en
 * @property-read \DateTimeInterface|null registration_begin
 * @property-read \DateTimeInterface|null registration_end
 * @property-read \DateTimeInterface|null modification_end
 */
class ScheduleGroupModel extends Model implements Resource, NodeCreator
{

    public const RESOURCE_ID = 'event.scheduleGroup';

    public function getItems(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SCHEDULE_ITEM);
    }

    public function getName(): array
    {
        return [
            'cs' => $this->name_cs,
            'en' => $this->name_en,
        ];
    }

    /**
     * Label include datetime from schedule group
     */
    public function getLabel(): string
    {
        return $this->name_cs . '/' . $this->name_en;
    }

    public function __toArray(): array
    {
        return [
            'scheduleGroupId' => $this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type->value,
            'registrationBegin' => $this->getRegistrationBegin(),
            'registrationEnd' => $this->getRegistrationEnd(),
            'modificationEnd' => $this->getModificationEnd(),
            'label' => $this->getName(),
            'name' => $this->getName(),
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

    /**
     * @param string $key
     * @return ScheduleGroupType|mixed|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'schedule_group_type':
                $value = ScheduleGroupType::tryFrom($value);
                break;
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
            'scheduleGroupId' => $this->schedule_group_id,
            'scheduleGroupType' => $this->schedule_group_type->value,
            'eventId' => $this->event_id,
            'start' => $this->start->format('c'),
            'end' => $this->end->format('c'),
        ], $document, $node);
        XMLHelper::fillArrayArgumentsToNode('lang', [
            'name' => $this->getName(),
        ], $document, $node);
        return $node;
    }
}
