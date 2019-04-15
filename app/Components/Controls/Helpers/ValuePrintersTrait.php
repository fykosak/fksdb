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
     * @return Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): Helpers\ValuePrinters\StringValueControl {
        return new Helpers\ValuePrinters\StringValueControl($this->traitTranslator, $this->layout);
    }

}
