<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Schedule;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;
use Fykosak\Utils\Price\Price;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

/**
 * @property-read int $schedule_item_id
 * @property-read int $schedule_group_id
 * @property-read ScheduleGroupModel $schedule_group
 * @property-read float|null $price_czk
 * @property-read float|null $price_eur
 * @property-read int|bool $payable
 * @property-read int|bool $available
 * @property-read string|null $name_cs
 * @property-read string|null $name_en
 * @property-read LocalizedString $name
 * @property-read int|null $capacity
 * @property-read string|null $description_cs
 * @property-read string|null $description_en
 * @property-read LocalizedString $description
 * @property-read string|null $long_description_cs
 * @property-read string|null $long_description_en
 * @property-read LocalizedString $long_description
 * @property-read DateTime|null $begin
 * @property-read DateTime|null $end
 * @phpstan-type SerializedScheduleItemModel array{
 *      scheduleGroupId:int,
 *      price:array<string, string>,
 *      totalCapacity:int|null,
 *      usedCapacity:int|null,
 *      scheduleItemId:int,
 *      name:array<string, string>,
 *      begin:DateTime,
 *      end:DateTime,
 *      available: bool,
 *      description:array<string, string>,
 *      longDescription:array<string, string>,
 * }
 */
final class ScheduleItemModel extends Model implements Resource, NodeCreator
{
    public const RESOURCE_ID = 'event.schedule.item';

    public function getBegin(): DateTime
    {
        return $this->begin ?? $this->schedule_group->start;
    }

    public function getEnd(): DateTime
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
     * @phpstan-return TypedGroupedSelection<PersonScheduleModel>
     */
    public function getInterested(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<PersonScheduleModel> $selection */
        $selection = $this->related(DbNames::TAB_PERSON_SCHEDULE);
        return $selection;
    }

    public function getUsedCapacity(bool $removeRef = false): int
    {
        //->where('state !=', PersonScheduleState::Cancelled)
        $query = $this->getInterested();
        if ($removeRef) {
            $query->unsetRefCache();
        }
        return $query->count();
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
     * @phpstan-return SerializedScheduleItemModel
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
            'name' => $this->name->__serialize(),
            'begin' => $this->getBegin(),
            'end' => $this->getEnd(),
            'description' => $this->description->__serialize(),
            'longDescription' => $this->long_description->__serialize(),
            'available' => (bool)$this->available,
        ];
    }

    /**
     * @param string $key
     * @return LocalizedString|mixed|Model
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {

        switch ($key) {
            case 'name':
                $value = new LocalizedString([
                    'cs' => $this->name_cs,
                    'en' => $this->name_en,
                ]);
                break;
            case 'description':
                $value = new LocalizedString([
                    'cs' => $this->description_cs,
                    'en' => $this->description_en,
                ]);
                break;
            case 'long_description':
                $value = new LocalizedString([
                    'cs' => $this->long_description_cs,
                    'en' => $this->long_description_en,
                ]);
                break;
            default:
                $value = parent::__get($key);
        }
        return $value;
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
            'scheduleGroupId' => (string)$this->schedule_group_id,
            'totalCapacity' => (string)$this->capacity,
            'usedCapacity' => (string)$this->getUsedCapacity(),
            'scheduleItemId' => (string)$this->schedule_item_id,
        ], $document, $node);
        XMLHelper::fillArrayArgumentsToNode('lang', [
            'description' => $this->description->__serialize(),
            'name' => $this->name->__serialize(),
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
