<?php

namespace FKSDB\Components\Grids\Payment;

use FKSDB\EventPayment\PriceCalculator\PriceCalculator;
use FKSDB\ORM\ModelEventPayment;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class PaymentDetailGrid
 * @package FKSDB\Components\Grids\Payment
 * @property FileTemplate $template
 */
class PaymentDetailGrid extends Control {
    /**
     * @var mixed
     */
    private $data;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var PriceCalculator
     */
    private $calculator;
    /**
     * @var ModelEventPayment
     */
    private $model;

    public function __construct(ModelEventPayment $model, ITranslator $translator, PriceCalculator $calculator, $data) {
        parent::__construct();
        $this->data = $data;
        $this->model = $model;
        $this->translator = $translator;
        $this->calculator = $calculator;
    }

    public function render() {

        $this->template->data = $this->calculator->getGridItems($this->data);
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PaymentDetailGrid.latte');
        $this->template->render();
    }


}
