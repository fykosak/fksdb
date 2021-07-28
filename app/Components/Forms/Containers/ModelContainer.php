<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\BaseControl;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 */
class ModelContainer extends ContainerWithOptions {

    /**
     * @param ActiveRow|iterable $data
     * @param bool $erase
     * @return static
     */
    public function setValues($data, bool $erase = false): self {
        if ($data instanceof ActiveRow || $data instanceof AbstractModelMulti) {
            $data = $data->toArray();
        }
        return parent::setValues($data, $erase);
    }

    /**
     * @param bool $value
     */
    public function setDisabled($value = true): void {
        /** @var BaseControl $component */
        foreach ($this->getComponents() as $component) {
            $component->setDisabled($value);
        }
    }
}
