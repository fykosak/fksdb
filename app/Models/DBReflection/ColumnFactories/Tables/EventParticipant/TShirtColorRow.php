<?php

namespace FKSDB\Models\DBReflection\ColumnFactories\Tables\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\DBReflection\ColumnFactories\Types\DefaultColumnFactory;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

/**
 * Class TShirtColorRow
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TShirtColorRow extends DefaultColumnFactory {
    /**
     * @param AbstractModelSingle|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        $value = $model->tshirt_color;
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        $container = Html::el('span');
        $container->addHtml(Html::el('i')->addAttributes([
            'style' => 'background-color: ' . $value,
            'class' => 't-shirt-color',
        ]));
        $container->addText($value);
        return $container;
    }

}
