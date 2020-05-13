<?php

namespace FKSDB\Components\Controls\Choosers;

use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property FileTemplate $template
 */
class ThemeSwitcher extends Control {
    /**
     * @var array
     */
    private $availableThemes = ['light', 'dark'];
    /**
     * @var Session
     */
    private $session;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * ThemeSwitcher constructor.
     * @param Session $session
     * @param ITranslator $translator
     */
    public function __construct(Session $session, ITranslator $translator) {
        parent::__construct();
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * @return SessionSection
     */
    private function getSession(): SessionSection {
        return $this->session->getSection('theme');
    }

    /**
     * @return string
     */
    public function getSelectedTheme(): string {
        $session = $this->getSession();
        return $session->theme ?: $this->availableThemes[0];
    }


    public function render() {
        $this->template->setTranslator($this->translator);
        $this->template->availableThemes = $this->availableThemes;
        $this->template->theme = $this->getSelectedTheme();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ThemeSwitcher.latte');
        $this->template->render();
    }

    /**
     * @param string $theme
     * @throws AbortException
     */
    public function handleChangeTheme(string $theme) {
        $session = $this->getSession();
        $session->theme = $theme;
        $this->redirect('this');
    }
}
