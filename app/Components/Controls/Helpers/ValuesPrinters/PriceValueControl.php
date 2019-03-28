<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Payment\IPaymentModel;
use Nette\Templating\FileTemplate;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class PriceValueControl extends AbstractValue {
    /**
     * @param IPaymentModel $model
     * @param string $title
     * @param string $accessKey
     */
    public function render(IPaymentModel $model, string $title, string $accessKey) {
        $this->beforeRender($title);;
        $this->template->model = $model;
        $this->template->accessKey = $accessKey;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PriceValue.latte');
        $this->template->render();
    }
}
