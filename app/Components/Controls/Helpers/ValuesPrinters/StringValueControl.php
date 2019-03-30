<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Templating\FileTemplate;

/**
 * Class BinaryValueControl
 * @property FileTemplate $template
 */
class StringValueControl extends AbstractValue {
    /**
     * @param AbstractModelSingle $model
     * @param string $title
     * @param string $accessKey
     */
    public function render(AbstractModelSingle $model, string $title, string $accessKey) {
        $this->beforeRender($title);
        $this->template->model = $model;
        $this->template->accessKey = $accessKey;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'StringValue.latte');
        $this->template->render();
    }
}
