<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\SelectBox;
use ServiceSchool;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @todo Implement AJAX loading 
 *       Should return school_id or null.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class SchoolSelect extends SelectBox {

    /**
     * @var ServiceSchool
     */
    private $schoolService;

    function __construct(ServiceSchool $schoolService, $label = null) {
        parent::__construct($label);

        $this->schoolService = $schoolService;
        $schools = $this->schoolService->getSchools()->fetchPairs('school_id', 'name_full');

        $this->setItems($schools);
    }

}
