<?php

namespace FKSDB\Components\DatabaseReflection\Payment;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelPayment;
use Nette\Application\UI\PresenterComponent;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * Class BankAccountRow
 * @package FKSDB\Components\DatabaseReflection\Payment
 */
class LinkRow extends AbstractPaymentRow {
    /**
     * @var PresenterComponent
     */
    private $presenterComponent;

    /**
     * IdRow constructor.
     * @param ITranslator $translator
     * @param PresenterComponent $presenterComponent
     */
    public function __construct(ITranslator $translator, PresenterComponent $presenterComponent) {
        parent::__construct($translator);
        $this->presenterComponent = $presenterComponent;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Payment id');
    }

    /**
     * @param ModelPayment|AbstractModelSingle $model
     * @return Html
     * @throws \Nette\Application\UI\InvalidLinkException
     * @inheritDoc
     */
    protected function createHtmlValue(AbstractModelSingle $model): Html {
        return Html::el('a')->addAttributes([
            'href' => $this->presenterComponent->getPresenter()->link(':Event:Payment:detail', [
                'eventId' => $model->getEvent()->event_id,
                'id' => $model->payment_id,
            ])
        ])->addText('#' . $model->getPaymentId());
    }
}
