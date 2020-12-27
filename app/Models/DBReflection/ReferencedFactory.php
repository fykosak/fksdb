<?php

namespace FKSDB\Models\DBReflection;

use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;

/**
 * Class ReferencedFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class ReferencedFactory {

    private string $modelClassName;
    /*
     * modelClassName => string FQN of class/interface that can be access via 'method'-field
     * method => method name, that return Model of $this->modelClassName
     */
    private ?array $referencedAccess;

    public function __construct(string $modelClassName, ?array $referencedAccess) {
        $this->referencedAccess = $referencedAccess;
        $this->modelClassName = $modelClassName;
    }

    /**
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     * @throws CannotAccessModelException
     * @throws BadTypeException
     */
    public function accessModel(AbstractModelSingle $model): ?AbstractModelSingle {
        // model is already instance of desired model
        if ($model instanceof $this->modelClassName) {
            return $model;
        }

        // if referenced access is not set and model is not desired model throw exception
        if (!isset($this->referencedAccess)) {
            throw new BadTypeException($this->modelClassName, $model);
        }
        return $this->accessReferencedModel($model);
    }

    public function getModelClassName(): string {
        return $this->modelClassName;
    }

    /**
     * try interface and access via get<Model>()
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     * @throws CannotAccessModelException
     * @throws BadTypeException
     */
    private function accessReferencedModel(AbstractModelSingle $model): ?AbstractModelSingle {
        if ($model instanceof $this->referencedAccess['modelClassName']) {
            $referencedModel = $model->{$this->referencedAccess['method']}();
            if ($referencedModel) {
                if ($referencedModel instanceof $this->modelClassName) {
                    return $referencedModel;
                }
                throw new BadTypeException($this->modelClassName, $referencedModel);
            }
            return null;
        }
        throw new CannotAccessModelException($this->modelClassName, $model);
    }
}
