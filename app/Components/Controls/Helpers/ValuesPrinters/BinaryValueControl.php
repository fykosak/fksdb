<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class BinaryValueControl extends PrimitiveValue {
    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'BinaryValue.latte';
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    public static function getGridValue(AbstractModelSingle $model, string $accessKey): Html {
        $value = $model->{$accessKey};
        if (\is_null($value)) {
            return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('not set'));
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
    public function createGridItem(AbstractModelSingle $model, string $accessKey): Html {
        return self::getGridValue($model, $accessKey);
    }
}
