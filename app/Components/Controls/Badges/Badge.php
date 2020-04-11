<?php

namespace FKSDB\Components\Controls\Badges;

use Nette\Application\UI\Control;
use Nette\Caching\Storages\FileStorage;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 * Class Badge
 * @package FKSDB\Components\Controls\Badges
 * @property-read FileTemplate $template
 */
abstract class Badge extends Control {
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * Badge constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        parent::__construct();
        $this->translator = $translator;
    }

    /**
     * @param mixed ...$args
     * @return Html
     */
    abstract public static function getHtml(...$args): Html;

    /**
     * @param mixed ...$args
     */
    public function render(...$args) {
        $this->template->html = static::getHtml(...$args);
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . '/layout.latte');
        $this->template->render();
    }
}
