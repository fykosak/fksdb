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
     * @param bool $hasPermissions
     */
    public function render(IPaymentModel $model, string $title, string $accessKey, bool $hasPermissions = true) {
        $this->beforeRender($title, $hasPermissions);
        $this->template->model = $model;
        $this->template->accessKey = $accessKey;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PriceValue.latte');
        $this->template->render();
    }
}
