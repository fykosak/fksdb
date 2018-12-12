<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\ORM\ModelPayment;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class PaymentStateLabel
 * @property FileTemplate $template
 * @package FKSDB\Components\Grids\Payment
 */
class PaymentStateLabel extends Control {
    /**
     * @var ModelPayment
     */
    private $modelPayment;
    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(ModelPayment $modelPayment, ITranslator $translator) {
        parent::__construct();
        $this->modelPayment = $modelPayment;
        $this->translator = $translator;
    }

    public function render() {
        $this->template->setTranslator($this->translator);
        $this->template->className = $this->modelPayment->getUIClass();

        $label = $this->modelPayment->state;
        switch ($this->modelPayment->state) {
            case ModelPayment::STATE_NEW:
                $label = _('Nová platba');
                break;
            case ModelPayment::STATE_WAITING:
                $label = _('Čaká na zaplatenie');
                break;
            case ModelPayment::STATE_CANCELED:
                $label = _('Zrušená platba');
                break;
            case ModelPayment::STATE_RECEIVED:
                $label = _('Platba prijatá');
        }

        $this->template->label = $label;

        $this->template->setFile(__DIR__ . '/PaymentStateLabel.latte');
        $this->template->render();
    }
}
