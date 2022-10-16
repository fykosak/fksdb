<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model;

/**
 * @property-read int contest_category_id
 * @property-read string label
 * @property-read string name_cs
 * @property-read string name_en
 */
class ContestCategoryModel extends Model
{
    public const FYKOS_4 = 'FYKOS_4';
    public const FYKOS_3 = 'FYKOS_3';
    public const FYKOS_2 = 'FYKOS_2';
    public const FYKOS_1 = 'FYKOS_1';
    public const VYFUK_9 = 'VYFUK_9';
    public const VYFUK_8 = 'VYFUK_8';
    public const VYFUK_7 = 'VYFUK_7';
    public const VYFUK_6 = 'VYFUK_6';
    public const VYFUK_UNK = 'VYFUK_UNK';
    public const ALL = 'ALL';

}
