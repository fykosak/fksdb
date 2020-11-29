<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\UI\Title;
use FKSDB\YearCalculator;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Security\User;

class YearChooser extends Chooser {

    public const ROLE_ORG = 'org';
    public const ROLE_CONTESTANT = 'contestant';
    public const ROLE_ALL = 'all';

    private int $year;
    private string $role;
    private ModelContest $contest;

    private User $user;
    private YearCalculator $yearCalculator;

    public function __construct(Container $container, int $urlYear, string $role, ModelContest $contest) {
        parent::__construct($container);
        $this->year = $urlYear;
        $this->role = $role;
        $this->contest = $contest;
    }

    public function injectPrimary(YearCalculator $yearCalculator, User $user): void {
        $this->user = $user;
        $this->yearCalculator = $yearCalculator;
    }

    protected function getTitle(): Title {
        return new Title(sprintf(_('Year %d'), $this->year));
    }

    protected function getItems(): iterable {
        return $this->yearCalculator->getAvailableYears($this->role, $this->contest, $this->user);
    }

    /**
     * @param int $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $item === $this->year;
    }

    /**
     * @param int $item
     * @return Title
     */
    public function getItemTitle($item): Title {
        return new Title(sprintf(_('Year %d'), $item));
    }

    /**
     * @param int $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['year' => $item]);
    }
}
