<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\ColumnPrinter;

use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\ORM\ORMFactory;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Model;

class ColumnRendererComponent extends BaseComponent
{
    private ORMFactory $tableReflectionFactory;

    final public function injectTableReflectionFactory(ORMFactory $tableReflectionFactory): void
    {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final public function renderTemplateString(string $templateString, ?Model $model, ?int $userPermission): void
    {
        $this->template->html = $this->renderToString($templateString, $model, $userPermission);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'string.latte');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final public function renderToString(string $templateString, ?Model $model, ?int $userPermission): string
    {
        return preg_replace_callback(
            '/@([a-z_]+).([a-z_]+)(:([a-zA-Z]+))?/',
            function (array $match) use ($model, $userPermission) {
                [, $table, $field, , $render] = $match;
                $factory = $this->tableReflectionFactory->loadColumnFactory($table, $field);
                switch ($render) {
                    default:
                    case 'value':
                        return $factory->render($model, $userPermission);
                    case 'title':
                        return $factory->getTitle();
                    case 'description':
                        return $factory->getDescription();
                }
            },
            $templateString
        );
    }

    /**
     * @throws CannotAccessModelException
     */
    final public function renderRow(
        string $field,
        Model $model,
        int $userPermission = FieldLevelPermission::ALLOW_FULL
    ): void {
        $this->template->model = $model;
        $this->template->userPermission = $userPermission;
        $this->template->name = $field;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.row.latte');
    }

    /**
     * @throws CannotAccessModelException
     */
    final public function renderListItem(
        string $field,
        Model $model,
        int $userPermission = FieldLevelPermission::ALLOW_FULL
    ): void {
        $this->template->model = $model;
        $this->template->userPermission = $userPermission;
        $this->template->name = $field;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.listItem.latte');
    }
}
