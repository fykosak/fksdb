<?php

namespace FKSDB\Models\ORM;

use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;

/**
 * Class ReferencedFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class ReferencedAccessor {

    /**
     * @param AbstractModelSingle $model
     * @param string $modelClassName
     * @return IModel|null
     * @throws CannotAccessModelException
     */
    public static function accessModel($model, string $modelClassName) {
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
