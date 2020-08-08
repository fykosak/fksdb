<?php

namespace FKSDB\Components\Forms\Factories\Events;

use FKSDB\Events\Model\Holder\DataValidator;
use FKSDB\Events\Model\Holder\Field;
use Nette\ComponentModel\IComponent;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IFieldFactory {

    /**
     * @param Field $field field for which it's created
     */
    public function createComponent(Field $field): IComponent;

    /**
     * For its own output, it must be able to find the control that may be used
     * for form rules (dependecies).
     *
     * @param IComponent $component
     * @return IControl
     */
    public function getMainControl(IComponent $component): IControl;

    /**
     * Checks whether data are filled correctly (more than form validation as the validity
     * can depend on the machine state).
     *
     * @param Field $field
     * @param DataValidator $validator
     * @return bool
     */
    public function validate(Field $field, DataValidator $validator);

    /**
     * @param IComponent $component
     * @param Field $field
     * @return void
     */
    public function setFieldDefaultValue(IComponent $component, Field $field);
}
