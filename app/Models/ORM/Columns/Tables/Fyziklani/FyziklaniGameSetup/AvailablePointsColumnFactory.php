<?php

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniGameSetup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Utils\Html;

class AvailablePointsColumnFactory extends ColumnFactory{
    /**
     * @param AbstractModel|ModelFyziklaniGameSetup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
        $container = Html::el('span');
        foreach ($model->getAvailablePoints() as $points) {
            $container->addHtml(Html::el('span')
                ->addAttributes(['class' => 'badge badge-secondary mr-1'])
                ->addText($points));
        }
        return $container;
    }
}
