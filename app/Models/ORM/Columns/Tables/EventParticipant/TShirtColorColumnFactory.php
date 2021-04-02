<?php

namespace FKSDB\Models\ORM\Columns\Tables\EventParticipant;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Utils\Html;

class TShirtColorColumnFactory extends ColumnFactory {
    /**
     * @param AbstractModel|ModelEventParticipant $model
     * @return Html
     */
    protected function createHtmlValue(AbstractModel $model): Html {
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
