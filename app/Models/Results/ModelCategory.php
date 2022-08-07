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

    public const CAT_HS_4 = '4';
    public const CAT_HS_3 = '3';
    public const CAT_HS_2 = '2';
    public const CAT_HS_1 = '1';
    public const CAT_ES_9 = '9';
    public const CAT_ES_8 = '8';
    public const CAT_ES_7 = '7';
    public const CAT_ES_6 = '6';
    public const CAT_UNK = 'UNK';
    public const CAT_ALL = 'ALL';

    public static function cases(): array
    {
        return [
            new self(self::CAT_HS_4),
            new self(self::CAT_HS_3),
            new self(self::CAT_HS_2),
            new self(self::CAT_HS_1),
            new self(self:: CAT_ES_9),
            new self(self::CAT_ES_8),
            new self(self:: CAT_ES_7),
            new self(self::CAT_ES_6),
            new self(self:: CAT_UNK),
            new self(self::CAT_ALL),
        ];
    }
}
