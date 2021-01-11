<?php

namespace FKSDB\Models\Events\Model\Holder;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DataValidator {

    use SmartObject;

    private array $validationErrors;

    public function validate(BaseHolder $baseHolder): void {
        // validate
        $this->validationErrors = [];
        $this->validateFields($baseHolder);
    }

    public function getValidationResult(): ?array {
        return count($this->validationErrors) ? $this->validationErrors : null;
    }

    private function validateFields(BaseHolder $baseHolder): void {
        foreach ($baseHolder->getFields() as $field) {
            $field->validate($this);
        }
    }

    public function addError(string $error): void {
        $this->validationErrors[] = $error;
    }
}
