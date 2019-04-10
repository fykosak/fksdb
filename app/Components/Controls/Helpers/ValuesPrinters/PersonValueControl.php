<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\BadSignalException;
use Nette\NotImplementedException;
use Nette\Utils\Html;

/**
 * Class PersonValueControl
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
class PersonValueControl extends AbstractValueControl {
    /**
     * @return string
     */
    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'PrimitiveValue.latte';
    }

    /**
     * @param AbstractModelSingle $model
     * @param string|null $title
     * @param string|null $accessKey
     * @param bool $hasPermissions
     */
    public function render(AbstractModelSingle $model, string $title = null, string $accessKey = null, bool $hasPermissions = true) {
        $this->beforeRender($title, $hasPermissions);
        $this->template->html = $this->getSafeHtml($model, $accessKey, $hasPermissions);
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     * @throws BadSignalException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    protected function getHtml(AbstractModelSingle $model, string $accessKey): Html {
        if (!$model instanceof ModelPerson) {
            throw new BadSignalException();
        }
        return Html::el('a')
            ->addAttributes(['href' => $this->getPresenter()->link(':Common:Stalking:view', [
                'id' => $model->person_id,
            ])])
            ->addText($model->getFullName());
    }

    /**
     * @param AbstractModelSingle $model
     * @param string $accessKey
     * @return Html
     */
    protected static function getHtmlStatic(AbstractModelSingle $model, string $accessKey): Html {
        throw new NotImplementedException();
    }
}
