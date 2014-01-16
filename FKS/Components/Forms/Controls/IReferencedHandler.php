<?php

namespace FKS\Components\Forms\Controls;

use Nette\ArrayHash;
use ORM\IModel;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedHandler {

    public function update(IModel $model, ArrayHash $values);

    public function createFromValues(ArrayHash $values);
}

class AlreadyExistsException extends RuntimeException {

    /** @var IModel */
    private $model;

    /** @var string */
    private $idName;

    public function __construct(IModel $model, $code = null, $previous = null) {
        $message = "Collision with existing model when creating model from values.";
        parent::__construct($message, $code, $previous);

        $this->model = $model;
    }

    public function getModel() {
        return $this->model;
    }

    public function getIdName() {
        return $this->idName;
    }

    public function setIdName($idName) {
        $this->idName = $idName;
    }

}
