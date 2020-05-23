<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Components\Controls\BaseComponent;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Templating\FileTemplate;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 * @property FileTemplate $template
 */
class ThemeSwitcher extends BaseComponent {
    /**
     * @var array
     */
    private $availableThemes = ['light', 'dark'];
    /**
     * @var Session
     */
    private $session;

    /**
     * ThemeSwitcher constructor.
     * @param Container $container
     * @param Session $session
     */
    public function __construct(Container $container, Session $session) {
        parent::__construct($container);
        $this->session = $session;
    }

    private function getSession(): SessionSection {
        return $this->session->getSection('theme');
    }

    public function getSelectedTheme(): string {
        $session = $this->getSession();
        return $session->theme ?: $this->availableThemes[0];
    }


    public function render() {
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
