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
     * @return Helpers\ValuePrinters\StringValueControl
     */
    public function createComponentStringValue(): Helpers\ValuePrinters\StringValueControl {
        return new Helpers\ValuePrinters\StringValueControl($this->translator);
    }
}
