<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\ColumnPrinter;

use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;

class ColumnTable extends BaseComponent
{
    protected function createComponentPrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
    /**
     * @phpstan-param string[] $fields
     * @throws CannotAccessModelException
     */
    final public function render(
        array $fields,
        Model $model,
        int $userPermission = FieldLevelPermission::ALLOW_FULL
    ): void {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'table.latte', [
            'model' => $model,
            'userPermission' => $userPermission,
            'fields' => $fields,
        ]);
    }
}
