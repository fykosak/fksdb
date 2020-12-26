<?php

namespace FKSDB\Models\ORM\Columns\Tables\Fyziklani\FyziklaniGameSetup;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Utils\Html;

/**
 * Class AvailablePointsRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AvailablePointsRow extends ColumnFactory{
    /**
     * @param AbstractModelSingle|ModelFyziklaniGameSetup $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $container = Html::el('span');
        foreach ($model->getAvailablePoints() as $points) {
            $container->addHtml(Html::el('span')
                ->addAttributes(['class' => 'badge badge-secondary mr-1'])
                ->addText($points));
        }
        return $container;
    }
}
