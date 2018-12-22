<?php


namespace FKSDB\Components\Forms\Controls\Payment;


use FKSDB\Payment\PriceCalculator\Price;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class PriceControl
 * @package FKSDB\Components\Forms\Controls\Payment
 * @property FileTemplate $template
 */
class PriceControl extends Control {
    /**
     * @var Price
     */
    private $price;
    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(ITranslator $translator, Price $price) {
        parent::__construct();
        $this->translator = $translator;
        $this->price = $price;
    }

    public function render() {
        $this->template->price = $this->price;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'PriceControl.latte');

        $this->template->render();
    }
}
