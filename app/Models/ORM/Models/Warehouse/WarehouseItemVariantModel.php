<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

/**
 * @property-read int $warehouse_item_variant_id
 * @property-read int $warehouse_item_id
 * @property-read WarehouseItemModel $warehouse_item
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read float|null $price_czk
 * @property-read float|null $price_eur
 * @property-read string|null $description_cs
 * @property-read string|null $description_en
 * @property-read int $available available in online store
 * @property-read int $total
 * @property-read DateTime $checked
 * @property-read string|null $placement kde je uskladnena
 * @property-read string|null $note neverejná poznámka
 */
final class WarehouseItemVariantModel extends Model implements Resource
{
    public const RESOURCE_ID = 'warehouse.item';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getName(): LocalizedString
    {
        return new LocalizedString(['cs' => $this->name_cs, 'en' => $this->name_en]);
    }
    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getDescription(): LocalizedString
    {
        return new LocalizedString(['cs' => $this->description_cs, 'en' => $this->description_en]);
    }
}
