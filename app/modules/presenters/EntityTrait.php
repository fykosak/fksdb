<?php

namespace FKSDB;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

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
            $row = $this->loadModel($id ?: $this->id);
            if (!$row) {
                throw new BadRequestException('Neexistující model.', 404);
            }
            $this->model = ($this->getModelClassName())::createFromActiveRow($row);
        }
        return $this->model;
    }

    /**
     * @param $id
     * @return \FKSDB\ORM\AbstractModelSingle
     */
    abstract protected function loadModel($id): ActiveRow;

    /**
     * @return string
     */
    abstract protected function getModelResource(): string;

    /**
     * @return string|AbstractModelMulti|AbstractModelSingle
     */
    abstract protected function getModelClassName(): string;
}
