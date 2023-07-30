<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniSubmit;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use Fykosak\NetteORM\Model;
use Nette\Utils\Html;

/**
 * @phpstan-extends ColumnFactory<SubmitModel>
 */
class PointsColumnFactory extends ColumnFactory
{
    /**
     * @param SubmitModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $el = Html::el('span');
        if (!\is_null($model->points)) {
            return $el->addText($model->points);
        }
        return $el->addAttributes(['class' => 'badge bg-warning'])->addText(_('revoked'));
    }
}
