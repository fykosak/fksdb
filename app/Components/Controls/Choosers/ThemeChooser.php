<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\UI\Title;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Http\Session;
use Nette\Http\SessionSection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ThemeChooser extends Chooser {
    /**
     * @var array
     */
    private $availableThemes = ['light', 'dark'];
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     * @return void
     */
    public function injectSession(Session $session) {
        $this->session = $session;
    }

    private function getSession(): SessionSection {
        return $this->session->getSection(self::class);
    }

    /**
     * @return string|null
     */
    public function getSelectedTheme() {
        $session = $this->getSession();
        return $session->theme ?: null;
    }

    /**
     * @param string $theme
     * @throws AbortException
     */
    public function handleChange(string $theme) {
        $session = $this->getSession();
        $session->theme = $theme;
        $this->redirect('this');
    }

    protected function getTitle(): Title {
        return new Title(_('Theme'));
    }

    /**
     * @return array|iterable|string[]
     */
    protected function getItems() {
        return $this->availableThemes;
    }

    /**
     * @param string $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->getSelectedTheme();
    }

    /**
     * @param string $item
     * @return string
     */
    public function getItemLabel($item): string {
        return $item;
    }

    /**
     * @param string $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->link('change', $item);
    }
}
