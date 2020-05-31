<?php

namespace FKSDB\Events\Model\Holder;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DataValidator {
    use SmartObject;

    /**
     * @var string[]
     */
    private $validationErrors;

    public function validate(BaseHolder $baseHolder): void {
        // validate
        $this->validationErrors = [];
        $this->validateFields($baseHolder);
    }

    /**
     * @return bool|string[]
     */
    public function getValidationResult() {
        return count($this->validationErrors) ? $this->validationErrors : true;
    }

    private function validateFields(BaseHolder $baseHolder): void {
        foreach ($baseHolder->getFields() as $field) {
            $field->validate($this);
        }
    }

    /**
     * @param string $error
     */
    public function addError($error) {
        $this->validationErrors[] = $error;
    }
}
