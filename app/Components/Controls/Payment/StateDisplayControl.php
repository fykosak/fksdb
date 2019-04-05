<?php

namespace FKSDB\Components\Controls\Payment;

use FKSDB\ORM\Models\ModelPayment;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class StateDisplayControl
 * @property FileTemplate $template
 * @package FKSDB\Components\Forms\Controls\Payment
 */
class StateDisplayControl extends Control {
    /**
     * @var \FKSDB\ORM\Models\ModelPayment
     */
    private $model;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * StateDisplayControl constructor.
     * @param ITranslator $translator
     * @param \FKSDB\ORM\Models\ModelPayment $model
     */
    public function __construct(ITranslator $translator, ModelPayment $model) {
        parent::__construct();
        $this->model = $model;
        $this->translator = $translator;
    }

    public function render() {
        $this->template->state = $this->model->state;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'StateDisplayControl.latte');
        $this->template->render();
    }
}
