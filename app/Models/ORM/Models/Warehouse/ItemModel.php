<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LocalizedString;
use Fykosak\Utils\Price\Currency;
use Nette\Security\Resource;

/**
 * @property-read int $item_id
 * @property-read int $product_id
 * @property-read ProductModel $product
 * @property-read int $contest_id
 * @property-read ContestModel $contest
 * @property-read string $state ENUM ('new','used','unpacked','damaged') NOT NULL,
 * @property-read string|null $description_cs
 * @property-read string|null $description_en
 * @property-read LocalizedString $description
 * @property-read string|null $data dalšie info
 * @property-read float|null $purchase_price pořizovací cena
 * @property-read string|null $purchase_currency pořizovací měna
 * @property-read \DateTimeInterface $checked
 * @property-read \DateTimeInterface|null $shipped kedy bola položka vyexpedovaná
 * @property-read int $available available in online store
 * @property-read string|null $placement kde je uskladnena
 * @property-read float|null $price price in FYKOS Coins
 * @property-read string|null $note neverejná poznámka
 */
final class ItemModel extends Model implements Resource
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
     * @phpstan-return ItemState|mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        switch ($key) {
            case 'description':
                $value = new LocalizedString(['cs' => $this->description_cs, 'en' => $this->description_en]);
                break;
            default:
                $value = parent::__get($key);
        }

        switch ($key) {
            case 'state':
                $value = ItemState::tryFrom($value);
                break;
        }
        return $value;
    }
}
