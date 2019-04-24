<?php

namespace FKSDB\Components\Controls\Helpers;

use FKSDB\Components\Controls\Helpers;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Class DetailControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 */
abstract class AbstractDetailControl extends Control {
    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * DetailControl constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        parent::__construct();
        $this->translator = $translator;
    }

    /**
     * @return Helpers\ValuePrinters\PhoneValueControl
     */
    public function createComponentPhoneValue(): Helpers\ValuePrinters\PhoneValueControl {
        return new Helpers\ValuePrinters\PhoneValueControl($this->translator);
    }

    /**
     * @return Helpers\ValuePrinters\IsSetValueControl
     */
    public function createComponentIsSetValue(): Helpers\ValuePrinters\IsSetValueControl {
        return new Helpers\ValuePrinters\IsSetValueControl($this->translator);
    }

    /**
     * @return Helpers\ValuePrinters\BinaryValueControl
     */
    public function createComponentBinaryValue(): Helpers\ValuePrinters\BinaryValueControl {
        return new Helpers\ValuePrinters\BinaryValueControl($this->translator);
    }

    /**
     * @return Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): Helpers\ValuePrinters\StringValueControl {
        return new Helpers\ValuePrinters\StringValueControl($this->translator);
    }
}
