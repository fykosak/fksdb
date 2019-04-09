<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class PhoneValueControl extends PrimitiveValue {
    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'PhoneNumber.latte';
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    public static function getGridValue(AbstractModelSingle $model, string $accessKey): Html {
        return PhoneNumberFactory::format($model->{$accessKey});
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
