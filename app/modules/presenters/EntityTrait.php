<?php

namespace FKSDB;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use Nette\Application\BadRequestException;
use Nette\ComponentModel\IComponent;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IResource;

/**
 * Trait EntityTrait
 */
trait EntityTrait {
    /**
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var
     */
    private $model;

    /**
     * @param int $id
     * @return \FKSDB\ORM\AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function getModel(int $id = null) {
        if (!$this->model) {
            $row = $this->loadRow($id ?: $this->id);
            if (!$row) {
                throw new BadRequestException('Neexistující model.', 404);
            }
            $this->model = ($this->getModelClassName())::createFromActiveRow($row);
        }
        return $this->model;
    }

    /**
     * @param int $id
     * @return ActiveRow|null
     */
    abstract protected function loadRow(int $id);

    /**
     * @return string|IResource
     */
    abstract protected function getModelResource();

    /**
     * @return string|AbstractModelMulti|AbstractModelSingle
     */
    abstract protected function getModelClassName(): string;

    /**
     * @return FormControl
     */
    abstract protected function createComponentCreateFormControl(): FormControl;

    /**
     * @return FormControl
     */
    abstract protected function createComponentEditFormControl(): FormControl;

    /**
     * @return BaseGrid
     */
    abstract protected function createComponentGrid(): BaseGrid;
}
