<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\Utils\Price\Currency;
use Nette\Security\Resource;

/**
 * @property-read int item_id
 * @property-read int product_id
 * @property-read ProductModel product
 * @property-read int contest_id
 * @property-read ContestModel contest
 * @property-read string state ENUM ('new','used','unpacked','damaged') NOT NULL,
 * @property-read string|null description_cs
 * @property-read string|null description_en
 * @property-read string|null data dalšie info
 * @property-read float|null purchase_price pořizovací cena
 * @property-read string|null purchase_currency pořizovací měna
 * @property-read \DateTimeInterface checked
 * @property-read \DateTimeInterface|null shipped kedy bola položka vyexpedovaná
 * @property-read int available available in online store
 * @property-read string|null placement kde je uskladnena
 * @property-read float|null price price in FYKOS Coins
 * @property-read string|null note neverejná poznámka
 */
class ItemModel extends Model implements Resource
{
    public const RESOURCE_ID = 'warehouse.item';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @throws \Exception
     */
    public function getCurrency(): Currency
    {
        return Currency::from($this->purchase_currency);
    }

    /**
     * @param string $key
     * @return ItemState|mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = ItemState::tryFrom($value);
                break;
        }
        return $value;
    }
}
