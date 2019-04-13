<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\DatabaseReflection\ValuePrinters\PricePrinter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\Payment\IPaymentModel;
use FKSDB\Payment\Price;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class PriceValueControl extends AbstractValueControl {
    /**
     * @param IPaymentModel|Price $model
     * @param string $title
     * @param string $accessKey
     * @param bool $hasPermissions
     */
    public function render($model, string $title, string $accessKey, bool $hasPermissions = true) {
        $this->beforeRender($title, $hasPermissions);
        if ($model instanceof Price) {
            $price = $model;
        } else {
            $price = null;
            if ($model->{$accessKey}) {
                $price = $model->getPrice();
            }
        }
        $this->template->html = (new PricePrinter)($price);

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PrimitiveValue.latte');
        $this->template->render();
    }

    /**
     * @param IPaymentModel|Price|AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    protected function getHtml(AbstractModelSingle $model, string $accessKey): Html {
        if ($model instanceof Price) {
            $price = $model;
        } else {
            $price = $model->getPrice();
        }
        return (new PricePrinter)($price);
    }
}
