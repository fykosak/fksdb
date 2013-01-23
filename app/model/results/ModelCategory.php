<?php

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
    const CAT_UNK  = 'UNK';

    public $id = null;

    function __construct($id) {
        $this->id = $id;
    }

}

?>
