<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\EntityForms\Processing\Postprocessing;
use FKSDB\Components\EntityForms\Processing\Preprocessing;
use FKSDB\Models\Utils\FormUtils;
use Fykosak\NetteORM\Model\Model;
use Nette\Database\Connection;
use Nette\Forms\Form;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TData of array
 * @phpstan-extends EntityFormComponent<TModel>
 */
abstract class ProcessedFormComponent extends EntityFormComponent
{
    protected Connection $connection;

    public function injectConnection(Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @throws \Throwable
     */
    final protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var TData $values */
        $values = $form->getValues('array');
        $values = FormUtils::emptyStrToNull2($values);
        $model = $this->connection->transaction(function () use ($form, $values): Model {
            $values = array_reduce(
                $this->getPreprocessing(),
                fn(array $prevValue, Preprocessing $item): array => $item(
                    $prevValue,
                    $form,
                    $this->model
                ),
                $values
            );
            $model = $this->innerSuccess($values, $form);
            foreach ($this->getPostprocessing() as $postprocessing) {
                $postprocessing($model);
            }
            return $model;
        });
        $this->successRedirect($model);
    }

    /**
     * @phpstan-param TData $values
     * @phpstan-return TModel
     */
    abstract protected function innerSuccess(array $values, Form $form): Model;

    /**
     * @phpstan-return Preprocessing<TModel,TData>[]
     */
    protected function getPreprocessing(): array
    {
        return [];
    }

    /**
     * @phpstan-return Postprocessing<TModel>[]
     */
    protected function getPostprocessing(): array
    {
        return [];
    }

    /**
     * @phpstan-param TModel $model
     */
    abstract protected function successRedirect(Model $model): void;
}
