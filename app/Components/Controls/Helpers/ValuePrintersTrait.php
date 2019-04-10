<?php

namespace FKSDB\Components\Controls\Helpers;

use BasePresenter;
use FKSDB\Components\Controls\Helpers;
use Nette\Localization\ITranslator;

/**
 * Trait ValuePrintersTrait
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @used-by BasePresenter
 */
trait ValuePrintersTrait {
    /**
     * @var ITranslator
     */
    private $traitTranslator;
    /**
     * @var string
     */
    private $layout;

    /**
     * @param ITranslator $translator
     * @param string $layout
     */
    public function registerTrait(ITranslator $translator, string $layout) {
        $this->traitTranslator = $translator;
        $this->layout = $layout;
    }

    /**
     * @return Helpers\ValuePrinters\PhoneValueControl
     */
    public function createComponentPhoneValue(): Helpers\ValuePrinters\PhoneValueControl {
        return new Helpers\ValuePrinters\PhoneValueControl($this->traitTranslator, $this->layout);
    }

    /**
     * @return Helpers\ValuePrinters\IsSetValueControl
     */
    public function createComponentIsSetValue(): Helpers\ValuePrinters\IsSetValueControl {
        return new Helpers\ValuePrinters\IsSetValueControl($this->traitTranslator, $this->layout);
    }

    /**
     * @return Helpers\ValuePrinters\BinaryValueControl
     */
    public function createComponentBinaryValue(): Helpers\ValuePrinters\BinaryValueControl {
        return new Helpers\ValuePrinters\BinaryValueControl($this->traitTranslator, $this->layout);
    }

    /**
     * @return Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): Helpers\ValuePrinters\StringValueControl {
        return new Helpers\ValuePrinters\StringValueControl($this->traitTranslator, $this->layout);
    }

    /**
     * @return Helpers\ValuePrinters\DateValueControl
     */
    public function createComponentDateValue(): Helpers\ValuePrinters\DateValueControl {
        return new Helpers\ValuePrinters\DateValueControl($this->translator);
    }
}
