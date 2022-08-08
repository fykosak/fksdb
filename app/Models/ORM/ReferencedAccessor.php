<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

final class ReferencedAccessor
{

    /**
     * @template T
     * @param class-string<T>|string $modelClassName
     * @return ?T
     */
    public static function accessModel(ActiveRow $model, string $modelClassName): ?ActiveRow
    {
        if ($model instanceof Model) {
            return \Fykosak\NetteORM\ReferencedAccessor::accessModel($model, $modelClassName);
        }

        // model is already instance of desired model
        if ($model instanceof $modelClassName) {
            return $model;
        }
        $modelReflection = new \ReflectionClass($model);
        $candidates = 0;
        foreach ($modelReflection->getMethods() as $method) {
            $name = $method->getName();
            $return = (string)$method->getReturnType();
            if (substr($return, 0, 1) == '?') {
                $return = substr($return, 1);
            }
            if ($return === $modelClassName) {
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
