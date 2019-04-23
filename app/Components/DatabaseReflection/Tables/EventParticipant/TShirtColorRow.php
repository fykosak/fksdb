<?php

namespace FKSDB\Components\DatabaseReflection\EventParticipant;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\Html;

/**
 * Class TShirtColor
 * @package FKSDB\Components\DatabaseReflection\EventParticipant
 */
class TShirtColorRow extends AbstractParticipantRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('T-shirt color');
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $fieldName
     * @return Html
     */
    protected function createHtmlValue(AbstractModelSingle $model, string $fieldName): Html {
        $value = $model->{$fieldName};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        }
        $container = Html::el('span');
        $container->addHtml(Html::el('i')->addAttributes(['style' =>
            'background-color: ' . $value . ';
                     height:1rem;
                     width: 1rem;
                     border-radius: .5rem;
                     display: inline-block;
                     margin-right: .5rem'
        ]));
        $container->addText($value);
        return $container;
    }

}
