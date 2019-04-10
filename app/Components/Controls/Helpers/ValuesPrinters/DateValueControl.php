<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\DateTime;
use Nette\Utils\Html;

/**
 * Class DateValueControl
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
class DateValueControl extends PrimitiveValueControl {

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    protected function getHtml(AbstractModelSingle $model, string $accessKey): Html {
        /**
         * @var DateTime $value
         */
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value->format('c'));
        }
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    protected static function getHtmlStatic(AbstractModelSingle $model, string $accessKey): Html {
        /**
         * @var DateTime $value
         */
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value->format('c'));
        }
    }
}
