<?php

namespace FKSDB\Models\ORM;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * Class ReferencedFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class ReferencedAccessor {

    public static function accessModel(ActiveRow $model, string $modelClassName): ?ActiveRow {
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
