<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\ColumnPrinter;

use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;

class ColumnTableComponent extends BaseComponent
{
    protected function createComponentPrinter(): ColumnRendererComponent
    {
        return new ColumnRendererComponent($this->getContext());
    }
    /**
     * @param string[] $fields
     * @throws CannotAccessModelException
     */
    final public function render(
        array $fields,
        Model $model,
        int $userPermission = FieldLevelPermission::ALLOW_FULL
    ): void {
        $this->template->model = $model;
        $this->template->userPermission = $userPermission;
        $this->template->fields = $fields;
        /** @phpstan-ignore-next-line */
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'table.latte');
    }
}
