<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\BaseControl;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @todo Implement AJAX loading + person filtering condition + (? person rendering options)
 *       Should return person_id or null.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class PersonSelect extends BaseControl {

    /**
     * @var ServicePerson
     */
    private $personService;

    function __construct(ServicePerson $personService, $label = null) {
        parent::__construct($label);
        
        $this->personService = $personService;
    }

}
