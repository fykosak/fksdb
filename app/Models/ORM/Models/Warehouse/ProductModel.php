<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\Security\Resource;

/**
 * @property-read int $product_id
 * @property-read int $producer_id
 * @property-read ProducerModel|null $producer
 * @property-read ProductCategory $category
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read LocalizedString $name
 * @property-read string $description_cs
 * @property-read string $description_en
 * @property-read LocalizedString $description
 * @property-read string $note neverejná poznámka
 * @property-read string $url URL k objednaniu produktu
 */
final class ProductModel extends Model implements Resource
{
    public const RESOURCE_ID = 'warehouse.product';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @param string $key
     * @return ProductCategory|mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        switch ($key) {
            case 'description':
                $value = new LocalizedString(['cs' => $this->description_cs, 'en' => $this->description_en]);
                break;
            case 'name':
                $value = new LocalizedString(['cs' => $this->name_cs, 'en' => $this->name_en]);
                break;
            default:
                $value = parent::__get($key);
        }
        switch ($key) {
            case 'category':
                $value = ProductCategory::tryFrom($value);
                break;
        }
        return $value;
    }
}
