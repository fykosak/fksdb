<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class SchoolElement extends \Nette\Forms\Controls\SelectBox {

    public function __construct($label, ServiceSchool $serviceSchool) {
        //TODO ajax našeptávač
        $items = $serviceSchool->getTable()->order('name_full')->fetchPairs('school_id', 'name_full');
        parent::__construct($label, $items, 1);
    }

}
