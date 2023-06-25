<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use Fykosak\NetteORM\Model;

/**
 * @todo Better (general) support for related collection setter.
 * @property-read int $tag_type_id
 * @property-read string $name
 * @property-read string $description
 * @property-read int $color
 */
class TagTypeModel extends Model
{
    public function getColor(): string
    {
        switch ($this->tag_type_id) {
            case 6:
                return 'dsef';
            case 4:
                return 'fol';
            case 1:
                return 'fof';
            case 15:
                return 'vyfuk';
            case 16:
                return 'fykos';
            default:
                return 'color-' . $this->color;
        }
    }
}
