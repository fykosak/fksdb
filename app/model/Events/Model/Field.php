<?php

namespace Events\Model;

use Nette\FreezableObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Field extends FreezableObject {

    /**
     * @var DataModel
     */
    private $dataModel;
    private $column;
    private $label;
    private $required;
    private $modifiable;
    private $visible;
    private $factory;

}
