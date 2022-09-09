<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use Nette\SmartObject;

class DataValidator
{
    use SmartObject;

    /** @var string[] */
    private array $validationErrors;

    public function validate(BaseHolder $baseHolder): ?array
    {
        foreach ($baseHolder->getFields() as $field) {
            $field->validate($this);
        }
        return $this->validationErrors ?? null;
    }

    public function addError(string $error): void
    {
        if (!isset($this->validationErrors)) {
            $this->validationErrors = [];
        }
        $this->validationErrors[] = $error;
    }
}
