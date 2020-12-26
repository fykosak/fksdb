<?php

namespace FKSDB\Models\ORM;

use FKSDB\Models\Entity\CannotAccessModelException;

/**
 * Class ReferencedFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class ReferencedFactory {

    /**
     * @param IModel $model
     * @param string $modelClassName
     * @return IModel|null
     * @throws CannotAccessModelException
     */
    public static function accessModel(IModel $model, string $modelClassName): ?IModel {
        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        $modelReflection = new \ReflectionClass($model);
        $candidates = 0;
        $newModel = null;
        foreach ($modelReflection->getMethods() as $method) {
            $name = (string)$method->getName();
            if ((string)$method->getReturnType() === $modelClassName) {
                $candidates++;
                $newModel = $model->{$name}();
            }
        }
        if ($candidates !== 1) {
            throw new CannotAccessModelException($modelClassName, $model);
        }
        return $newModel;
    }
}
