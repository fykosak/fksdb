<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class BinaryValueControl extends PrimitiveValueControl {

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    protected function getHtml(AbstractModelSingle $model, string $accessKey): Html {
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } elseif ($value) {
            return Html::el('span')->addAttributes(['class' => 'fa fa-check text-success']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fa fa-times text-danger']);
        }
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    protected static function getHtmlStatic(AbstractModelSingle $model, string $accessKey): Html {
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } elseif ($value) {
            return Html::el('span')->addAttributes(['class' => 'fa fa-check text-success']);
        } else {
            return Html::el('span')->addAttributes(['class' => 'fa fa-times text-danger']);
        }
    }


}
