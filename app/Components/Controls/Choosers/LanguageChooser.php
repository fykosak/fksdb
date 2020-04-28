<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\LangPresenterTrait;
use FKSDB\UI\Title;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class LanguageChooser extends Chooser {
    /** @var array */
    private $supportedLanguages;

    /** @var string */
    private $language;

    /** @var bool */
    private $modifiable;

    /**
     * @param Container $container
     * @param bool $modifiable
     */
    function __construct(Container $container, bool $modifiable) {
        parent::__construct($container);
        $this->modifiable = $modifiable;
    }

    /**
     * Redirect to correct address accorging to the resolved values.
     * @param string $lang
     * @throws \Exception
     */
    public function setLang(string $lang) {
        $this->language = $lang;
    }

    /**
     * @param string|null $class
     */
    public function render(string $class = null) {
        $this->beforeRender();
        $this->template->modifiable = $this->modifiable;
        $this->template->languageNames = LangPresenterTrait::$languageNames;
        $this->template->currentLanguage = $this->language ?: null;
        $this->template->class = $class ?: 'nav navbar-nav navbar-right';

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'LanguageChooser.latte');
        $this->template->render();
    }

    public function getTitle(): Title {
        return new Title(_('Language'), 'fa fa-language');
    }

    /**
     * @return array|iterable|void
     * @throws BadTypeException
     */
    public function getItems() {
        if (!count($this->supportedLanguages)) {
            $presenter = $this->getPresenter();
            if (!$presenter instanceof \BasePresenter) {
                throw new BadTypeException(\BasePresenter::class, $presenter);
            }
            $this->supportedLanguages = $presenter->getTranslator()->getSupportedLanguages();
        }
        return $this->supportedLanguages;
    }

    /**
     * @param string $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $this->language === $item;
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemLabel($item): string {
        return LangPresenterTrait::$languageNames[$item];
    }

    /**
     * @param string $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['lang' => $item]);
    }
}
