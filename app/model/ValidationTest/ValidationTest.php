<?php

namespace FKSDB\ValidationTest;

use ModelPerson;
use Nette\Application\UI\Control;

/**
 * Class ValidationTest
 */
abstract class ValidationTest {
    /**
     * @return string
     */
    abstract function run(ModelPerson $person);

    /**
     * @return string
     */
    abstract function getTitle(): string;

    /**
     * @return string
     */
    abstract function getAction(): string;

    /**
     * @return Control
     */
    abstract function getComponent(): Control;

}
