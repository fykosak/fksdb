<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\ColumnPrinter;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;

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
    final public function renderTemplateString(string $templateString, Model $model, ?int $userPermission): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'string.latte',
            ['html' => $this->renderToString($templateString, $model, $userPermission)]
        );
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    final public function renderToString(string $templateString, ?Model $model, ?int $userPermission): string
    {
        return preg_replace_callback(
            '/@([a-z_]+)\.([a-z_]+)(:([a-zA-Z]+))?/',
            function (array $match) use ($model, $userPermission) {
                [, $table, $field, , $render] = $match;
                $factory = $this->tableReflectionFactory->loadColumnFactory($table, $field);
                switch ($render) {
                    default:
                    case 'value':
                        if (!$model) {
                            throw new \InvalidArgumentException(_('"value" is available only with model'));
                        }
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
     * @deprecated
     */
    final public function renderListItem(
        string $field,
        Model $model,
        int $userPermission = FieldLevelPermission::ALLOW_FULL
    ): void {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.listItem.latte', [
            'model' => $model,
            'userPermission' => $userPermission,
            'name' => $field,
        ]);
    }
}
