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
class StringValueControl extends PrimitiveValueControl {

    /**
 * @param AbstractModelSingle $model
 * @param string $accessKey
 * @return Html
 */
    public function getHtml(AbstractModelSingle $model, string $accessKey): Html {
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value);
        }
    }
    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    public static function getHtmlStatic(AbstractModelSingle $model, string $accessKey): Html {
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return NotSetBadge::getHtml();
        } else {
            return Html::el('span')->addText($value);
        }
    }
}
