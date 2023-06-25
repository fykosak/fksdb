<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniGameSetup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\Fyziklani\GameSetupModel;
use Nette\Utils\Html;

class AvailablePointsColumnFactory extends ColumnFactory
{
    /**
     * @param GameSetupModel $model
     */
    protected function createHtmlValue(Model $model): Html
    {
        $container = Html::el('span');
        foreach ($model->getAvailablePoints() as $points) {
            $container->addHtml(
                Html::el('span')
                    ->addAttributes(['class' => 'badge bg-secondary me-1'])
                    ->addText($points)
            );
        }
        return $container;
    }
}
