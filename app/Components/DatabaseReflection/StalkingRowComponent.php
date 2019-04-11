<?php

namespace FKSDB\Components\DatabaseReflection;

use FKSDB\Components\Controls\Helpers\ValuePrinters\AbstractValue;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingRowComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
class StalkingRowComponent extends Control {
    /**
     * @var callable
     */
    private $callback;
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var string
     */
    private $title;

    /**
     * StalkingRowComponent constructor.
     * @param ITranslator $translator
     * @param callable $callback
     * @param string $title
     */
    public function __construct(ITranslator $translator, callable $callback, string $title) {
        parent::__construct();
        $this->callback = $callback;
        $this->translator = $translator;
        $this->title = $title;
    }

    /**
     * @return string
     */
    protected function getLayout(): string {
        return AbstractValue::LAYOUT_STALKING;
    }

    /**
     * @param AbstractModelSingle $model
     */
    public function render(AbstractModelSingle $model) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $this->title;
        $this->template->layout = $this->getLayout();
        $this->template->html = ($this->callback)($model);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }

}
