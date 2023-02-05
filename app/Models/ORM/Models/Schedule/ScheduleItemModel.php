<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\LocalizedString;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;
use Fykosak\Utils\Price\Price;
use Nette\Security\Resource;

/**
 * @property-read int schedule_item_id
 * @property-read int schedule_group_id
 * @property-read ScheduleGroupModel schedule_group
 * @property-read float|null price_czk
 * @property-read float|null price_eur
 * @property-read string|null name_cs
 * @property-read string|null name_en
 * @property-read int|null capacity
 * @property-read string|null description_cs
 * @property-read string|null description_en
 * @property-read string|null long_description_cs
 * @property-read string|null long_description_en
 * @property-read \DateTimeInterface|null begin
 * @property-read \DateTimeInterface|null end
 */
class ScheduleItemModel extends Model implements Resource, NodeCreator
{
    public const RESOURCE_ID = 'event.scheduleItem';

    public function getName(): LocalizedString
    {
        return new LocalizedString([
            'cs' => $this->name_cs,
            'en' => $this->name_en,
        ]);
    }

    public function getDescription(): LocalizedString
    {
        return new LocalizedString([
            'cs' => $this->description_cs,
            'en' => $this->description_en,
        ]);
    }

    public function getLongDescription(): LocalizedString
    {
        return new LocalizedString([
            'cs' => $this->long_description_cs,
            'en' => $this->long_description_en,
        ]);
    }

    public function getBegin(): \DateTimeInterface
    {
        return $this->begin ?? $this->schedule_group->start;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end ?? $this->schedule_group->end;
    }

    /**
     * @throws \Exception
     */
    public function getPrice(): MultiCurrencyPrice
    {
        $items = [];
        if (isset($this->price_eur)) {
            $items[] = new Price(Currency::from(Currency::EUR), +$this->price_eur);
        }
        if (isset($this->price_czk)) {
            $items[] = new Price(Currency::from(Currency::CZK), +$this->price_czk);
        }
        return new MultiCurrencyPrice($items);
    }

    /**
     * @throws \Exception
     */
    public function isPayable(): bool
    {
        return (bool)count($this->getPrice()->getPrices());
    }

    public function getInterested(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE);
    }

    /* ****** CAPACITY CALCULATION *******/
    public function getUsedCapacity(): int
    {
        return $this->getInterested()->count();
    }

    public function hasFreeCapacity(): bool
    {
        if (is_null($this->capacity)) {
            return true;
        }
        return ($this->capacity - $this->getUsedCapacity()) > 0;
    }

    /**
     * @throws \LogicException
     */
    public function getAvailableCapacity(): int
    {
        if (is_null($this->capacity)) {
            throw new \LogicException(_('Unlimited capacity'));
        }
        return ($this->capacity - $this->getUsedCapacity());
    }

    /**
     * @throws \Exception
     */
    public function __toArray(): array
    {
        return [
            'scheduleGroupId' => $this->schedule_group_id,
            'price' => $this->getPrice()->__serialize(),
            'totalCapacity' => $this->capacity,
            'usedCapacity' => $this->getUsedCapacity(),
            'scheduleItemId' => $this->schedule_item_id,
            'label' => $this->getName()->__serialize(),
            'name' => $this->getName()->__serialize(),
            'begin' => $this->getBegin(),
            'end' => $this->getEnd(),
            'description' => $this->getDescription()->__serialize(),
            'longDescription' => $this->getLongDescription()->__serialize(),
        ];
    }

    /**
     * @throws \DOMException
     * @throws \Exception
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('scheduleItem');
        $node->setAttribute('scheduleItemId', (string)$this->schedule_item_id);
        XMLHelper::fillArrayToNode([
            'scheduleGroupId' => $this->schedule_group_id,
            'totalCapacity' => $this->capacity,
            'usedCapacity' => $this->getUsedCapacity(),
            'scheduleItemId' => $this->schedule_item_id,
        ], $document, $node);
        XMLHelper::fillArrayArgumentsToNode('lang', [
            'description' => $this->getDescription()->__serialize(),
            'name' => $this->getName()->__serialize(),
        ], $document, $node);
        XMLHelper::fillArrayArgumentsToNode('currency', [
            'price' => $this->getPrice()->__serialize(),
        ], $document, $node);
        return $node;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
