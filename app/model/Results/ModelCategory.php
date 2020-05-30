<?php

namespace FKSDB\Results;
/**
 * POD, not represented in database
 *
 * @author michal
 */
class ModelCategory {

    const CAT_HS_4 = '4';
    const CAT_HS_3 = '3';
    const CAT_HS_2 = '2';
    const CAT_HS_1 = '1';
    const CAT_ES_9 = '9';
    const CAT_ES_8 = '8';
    const CAT_ES_7 = '7';
    const CAT_ES_6 = '6';
    const CAT_UNK = 'UNK';
    const CAT_ALL = 'ALL';

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
