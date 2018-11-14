<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\ORM\ModelEventPayment;
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
     * @var ModelEventPayment
     */
    private $modelEventPayment;
    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(ModelEventPayment $modelEventPayment, ITranslator $translator) {
        parent::__construct();
        $this->modelEventPayment = $modelEventPayment;
        $this->translator = $translator;
    }

    public function render() {
        $this->template->setTranslator($this->translator);
        $this->template->className = $this->modelEventPayment->getUIClass();

        $label = $this->modelEventPayment->state;
        switch ($this->modelEventPayment->state) {
            case ModelEventPayment::STATE_NEW:
                $label = _('Nová platba');
                break;
            case ModelEventPayment::STATE_WAITING:
                $label = _('Čaká na zaplatenie');
                break;
            case ModelEventPayment::STATE_CANCELED:
                $label = _('Zrušená platba');
                break;
            case ModelEventPayment::STATE_CONFIRMED:
                $label = _('Platba prijatá');
        }

        $this->template->label = $label;

        $this->template->setFile(__DIR__ . '/PaymentStateLabel.latte');
        $this->template->render();
    }
}
