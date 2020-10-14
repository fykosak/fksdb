<?php

namespace FKSDB\ORM\Models\Warehouse;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Security\IResource;

/**
 * Class ModelProduct
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read int product_id
 * @property-read int producer_id
 * @property-read string category
 * @property-read string name_cs
 * @property-read string name_en
 * @property-read string description_cs
 * @property-read string description_en
 * @property-read string note neverejná poznámka
 * @property-read string url URL k objednaniu produktu
 */
class ModelProduct extends AbstractModelSingle implements IResource {

    public const CATEGORY_APPAREL = 'apparel';
    public const CATEGORY_GAME = 'game';
    public const CATEGORY_GAME_EXTENSION = 'game-extension';
    public const CATEGORY_BOOK = 'book';
    public const CATEGORY_OTHER = 'other';

    public const RESOURCE_ID = 'warehouse.product';

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

}
