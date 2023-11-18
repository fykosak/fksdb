<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Task;

use FKSDB\Models\ORM\Columns\Types\AbstractColumnFactory;
use FKSDB\Models\ORM\Models\TaskModel;
use Fykosak\NetteORM\Model\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends AbstractColumnFactory<TaskModel,never>
 */
final class SolversCountColumnFactory extends AbstractColumnFactory
{
    /**
     * @param TaskModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        return Html::el('span')->addText($model->getTaskStats()['solversCount']);
    }
}
