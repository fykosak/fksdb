<?php

namespace FKSDB\DBReflection\ColumnFactories\Fyziklani\FyziklaniGameSetup;

use FKSDB\DBReflection\ColumnFactories\DefaultColumnFactory;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Utils\Html;

/**
 * Class AvailablePointsRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AvailablePointsRow extends DefaultColumnFactory {
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
