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
        $class = 'badge ';
        switch ($this->modelEventPayment->state) {
            case  ModelEventPayment::STATE_WAITING:
                $class .= 'badge-warning';
                break;
            case ModelEventPayment::STATE_CONFIRMED:
                $class .= 'badge-success';
                break;
            case ModelEventPayment::STATE_CANCELED:
                $class .= 'badge-secondary';
                break;
            case ModelEventPayment::STATE_NEW:
                $class .= 'badge-primary';
                break;
            default:
                $class .= 'badge-light';
        }
        $this->template->setTranslator($this->translator);
        $this->template->className = $class;
        $this->template->label = $this->modelEventPayment->state;
        $this->template->setFile(__DIR__ . 'PaymentStateLabel.latte');
        $this->template->render();
    }
}
