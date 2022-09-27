<?php

declare(strict_types=1);

namespace FKSDB\Models\Results;

use FKSDB\Models\Utils\FakeStringEnum;

/**
 * TODO to enum
 * POD, not represented in database
 */
class ModelCategory extends FakeStringEnum
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

    public static function cases(): array
    {
        return [
            new self(self::FYKOS_4),
            new self(self::FYKOS_3),
            new self(self::FYKOS_2),
            new self(self::FYKOS_1),
            new self(self::VYFUK_9),
            new self(self::VYFUK_8),
            new self(self::VYFUK_7),
            new self(self::VYFUK_6),
            new self(self::VYFUK_UNK),
            new self(self::ALL),
        ];
    }
}
