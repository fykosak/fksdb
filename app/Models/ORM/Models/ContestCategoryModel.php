<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Localization\LangMap;

/**
 * @property-read int $contest_category_id
 * @property-read string $label
 * @property-read string $name_cs
 * @property-read string $name_en
 * @property-read LangMap<'cs'|'en',string> $name
 */
final class ContestCategoryModel extends Model
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

    /**
     * @throws \ReflectionException
     */
    public function &__get(string $key): mixed // phpcs:ignore
    {
        switch ($key) {
            case 'name':
                $value = new LangMap(['cs' => $this->name_cs, 'en' => $this->name_en]);
                break;
            default:
                $value = parent::__get($key);
        }
        return $value;
    }
}
