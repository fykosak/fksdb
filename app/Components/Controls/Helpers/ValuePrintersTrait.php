<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use BasePresenter;
use FKSDB\Components\Controls\Helpers;

/**
 * Trait ValuePrintersTrait
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @used-by BasePresenter
 */
trait ValuePrintersTrait {

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

    /**
     * @return Helpers\ValuePrinters\DateValueControl
     */
    public function createComponentDateValue(): Helpers\ValuePrinters\DateValueControl {
        return new Helpers\ValuePrinters\DateValueControl($this->translator);
    }
}
