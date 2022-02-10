<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\BaseControl;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 */
class ModelContainer extends ContainerWithOptions
{
    /**
     * @param ActiveRow|iterable $data
     * @return static
     */
    public function setValues($data, bool $erase = false): self
    {
        if ($data instanceof ActiveRow) {
            $data = $data->toArray();
        }
        return parent::setValues($data, $erase);
    }

    public function setDisabled(bool $value = true): void
    {
        /** @var BaseControl $component */
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }
}
