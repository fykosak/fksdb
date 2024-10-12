<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Security\Resource;

/**
 * @property-read int $warehouse_item_id
 * @property-read ItemCategory $category
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read string $description_cs
 * @property-read string $description_en
 * @property-read string $note neverejná poznámka
 */
final class WarehouseItemModel extends Model implements Resource
{
    public const RESOURCE_ID = 'warehouse.product';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getDescription(): LocalizedString
    {
        return new LocalizedString(['cs' => $this->description_cs, 'en' => $this->description_en]);
    }

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getName(): LocalizedString
    {
        return new LocalizedString(['cs' => $this->name_cs, 'en' => $this->name_en]);
    }

    /**
     * @return ItemCategory|mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'category':
                $value = ItemCategory::tryFrom($value);
                break;
        }
        return $value;
    }
}
