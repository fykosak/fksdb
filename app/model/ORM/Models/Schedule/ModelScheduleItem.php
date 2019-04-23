<?php


namespace FKSDB\ORM\Models\Schedule;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\Payment\Price;
use FKSDB\Payment\PriceCalculator\UnsupportedCurrencyException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\NotImplementedException;

/**
 * Class ModelScheduleItem
 * @package FKSDB\ORM\Models\Schedule
 * @property-read ActiveRow schedule_group
 * @property-read float price_eur
 * @property-read float price_czk
 * @property-read int capacity
 * @property-read int schedule_item_id
 * @property-read int schedule_group_id
 * @property-read string name_cs
 * @property-read string name_en
 * @property-read int require_id_number
 */
class ModelScheduleItem extends AbstractModelSingle {
    /**
     * @return ModelScheduleGroup
     */
    public function getGroup(): ModelScheduleGroup {
        return ModelScheduleGroup::createFromTableRow($this->schedule_group);
    }

    /**
     * @param string $currency
     * @return Price
     * @throws UnsupportedCurrencyException
     */
    public function getPrice(string $currency): Price {
        switch ($currency) {
            case Price::CURRENCY_EUR:
                return new Price($this->price_eur, $currency);
            case Price::CURRENCY_CZK:
                return new Price($this->price_czk, $currency);
            default:
                throw new UnsupportedCurrencyException($currency);
        }
    }

    /**
     * @return integer
     */
    public function getCapacity(): int {
        return $this->capacity;
    }

    /**
     * @return GroupedSelection
     */
    public function getInterested(): GroupedSelection {
        return $this->related(DbNames::TAB_PERSON_SCHEDULE);
    }

    /**
     * @return int
     */
    public function getUsedCapacity(): int {
        return $this->getInterested()->count();
    }

    /**
     * @return int
     */
    public function getFreeCapacity(): int {
        return $this->getAvailableCapacity();
    }

    /**
     * @return int
     */
    public function getAvailableCapacity(): int {
        return ($this->getCapacity() - $this->getUsedCapacity());
    }

    /**
     * @return string
     * @throws NotImplementedException
     */
    public function getLabel(): string {
        $group = $this->getGroup();
        switch ($group->schedule_group_type) {
            case ModelScheduleGroup::TYPE_ACCOMMODATION:
                return \sprintf(_('Accommodation in "%s" from %s to %s'),
                    $this->name_cs,
                    $group->start->format('d. m. Y'),
                    $group->end->format('d. m. Y')
                );
        }
        throw new NotImplementedException();
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->getLabel();
    }
}
