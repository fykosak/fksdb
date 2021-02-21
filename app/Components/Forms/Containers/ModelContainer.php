<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\BaseControl;

/**
 * Formulářový kontejder reprezentující záznam z DB tabulky.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelContainer extends ContainerWithOptions {

    /**
     * @param ActiveRow|iterable $values
     * @param bool $erase
     * @return static
     */
    public function setValues($values, bool $erase = false): self {
        if ($values instanceof ActiveRow || $values instanceof AbstractModelMulti) {
            $values = $values->toArray();
        }
        return parent::setValues($values, $erase);
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
