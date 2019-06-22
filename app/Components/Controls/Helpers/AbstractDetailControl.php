<?php

namespace FKSDB\Components\Controls\Helpers;

use FKSDB\Components\Controls\Helpers\ValuePrinters\StringValueControl;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 * Class DetailControl
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @deprecated
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
     * @return StringValueControl
     */
    public function createComponentStringValue(): StringValueControl {
        return new StringValueControl($this->translator);
    }
}
