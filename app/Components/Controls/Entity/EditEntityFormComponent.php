<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Class EditEntityFormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class EditEntityFormComponent extends AbstractEntityFormComponent implements IEditEntityForm {

    protected AbstractModelSingle $model;

    public function setModel(AbstractModelSingle $model): void {
        $this->model = $model;
    }

    public function render(): void {
        $this->setDefaults($this->model ?? null);
        parent::render();
    }

    abstract protected function setDefaults(?AbstractModelSingle $model): void;
}
