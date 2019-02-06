<?php

namespace FKSDB\Components\Controls\Payment;

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

    /**
     * PaymentStateLabel constructor.
     * @param ModelPayment $modelPayment
     * @param ITranslator $translator
     */
    public function __construct(ModelPayment $modelPayment, ITranslator $translator) {
        parent::__construct();
        $this->modelPayment = $modelPayment;
        $this->translator = $translator;
    }

    public function render() {
        $this->template->setTranslator($this->translator);
        $this->template->className = $this->modelPayment->getUIClass();
        $this->template->label = $this->modelPayment->getStateLabel();
        $this->template->setFile(__DIR__ . '/PaymentStateLabel.latte');
        $this->template->render();
    }

}
