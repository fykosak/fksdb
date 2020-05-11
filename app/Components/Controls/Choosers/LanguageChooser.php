<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\LangPresenterTrait;
use FKSDB\UI\Title;
use Nette\Application\UI\InvalidLinkException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Jakub Šafin <xellos@fykos.cz>
 * @author Michal Červeňák <miso@fykos.cz>
 */
class LanguageChooser extends Chooser {
    /** @var array */
    private $supportedLanguages = [];

    /** @var string */
    private $language;

    /** @var bool */
    private $modifiable;

    /**
     * @param string $lang
     * @param bool $modifiable
     * @throws \Exception
     */
    public function setLang(string $lang , bool $modifiable) {
        $this->language = $lang;
        $this->modifiable = $modifiable;
    }

    public function render() {
        $this->beforeRender();
        $this->template->modifiable = $this->modifiable;
        $this->template->currentLanguageName = LangPresenterTrait::$languageNames[$this->language] ?: null;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'LanguageChooser.latte');
        $this->template->render();
    }

    public function getTitle(): Title {
        return new Title(isset(LangPresenterTrait::$languageNames[$this->language]) ? LangPresenterTrait::$languageNames[$this->language] : _('Language'), 'fa fa-language');
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
