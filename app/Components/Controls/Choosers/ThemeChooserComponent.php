<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\UI\Title;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Http\Session;
use Nette\Http\SessionSection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ThemeChooserComponent extends ChooserComponent {

    private const AVAILABLE_THEMES = ['light', 'dark'];

    private Session $session;

    final public function injectSession(Session $session): void {
        $this->session = $session;
    }

    private function getSession(): SessionSection {
        return $this->session->getSection(self::class);
    }

    public function getSelectedTheme(): ?string {
        $session = $this->getSession();
        return $session->theme;
    }

    /**
     * @param string $theme
     * @throws AbortException
     */
    public function handleChange(string $theme): void {
        $session = $this->getSession();
        $session->theme = $theme;
        $this->redirect('this');
    }

    protected function getTitle(): Title {
        return new Title(_('Theme'));
    }

    protected function getItems(): array {
        return self::AVAILABLE_THEMES;
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
     * @return Title
     */
    public function getItemTitle($item): Title {
        return new Title($item);
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
