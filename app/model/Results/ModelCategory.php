<?php

namespace FKSDB\Results;
/**
 * POD, not represented in database
 *
 * @author michal
 */
class ModelCategory {

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

    /** @var string */
    public $id;

    /**
     * FKSDB\Results\ModelCategory constructor.
     * @param $id
     */
    public function __construct(string $id) {
        $this->id = $id;
    }
}
