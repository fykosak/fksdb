<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Warehouse;

use FKSDB\Models\ORM\Models\ModelContest;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;

/**
 * @property-read int item_id
 * @property-read int product_id
 * @property-read ActiveRow product
 * @property-read int contest_id
 * @property-read ActiveRow contest
 * @property-read string state ENUM ('new','used','unpacked','damaged') NOT NULL,
 * @property-read string|null description_cs
 * @property-read string|null description_en
 * @property-read string|null data dalšie info
 * @property-read float|null purchase_price pořizovací cena
 * @property-read string|null purchase_currency pořizovací měna
 * @property-read \DateTimeInterface checked
 * @property-read \DateTimeInterface|null shipped kedy bola položka vyexpedovaná
 * @property-read bool available available in online store
 * @property-read string|null placement kde je uskladnena
 * @property-read float|null price price in FYKOS Coins
 * @property-read string|null note neverejná poznámka
 */
class ModelItem extends AbstractModel implements Resource
{
    public const RESOURCE_ID = 'warehouse.item';

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function getContest(): ModelContest
    {
        return ModelContest::createFromActiveRow($this->contest);
    }

    public function getProducer(): ?ModelProducer
    {
        return $this->getProduct()->getProducer();
    }

    public function getProduct(): ModelProduct
    {
        return ModelProduct::createFromActiveRow($this->product);
    }
}
