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

    /** @var string[] */
    private array $validationErrors;

    public function validate(BaseHolder $baseHolder): void {
        // validate
        $this->validateFields($baseHolder);
    }

    /**
     * @return null|string[]
     */
    public function getValidationResult(): ?array {
        return $this->validationErrors ?? null;
    }

    private function validateFields(BaseHolder $baseHolder): void {
        foreach ($baseHolder->getFields() as $field) {
            $field->validate($this);
        }
    }

    public function addError(string $error): void {
        if (!isset($this->validationErrors)) {
            $this->validationErrors = [];
        }
        $this->validationErrors[] = $error;
    }
}
