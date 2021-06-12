<?php

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\UI\Title;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestChooserComponent extends ChooserComponent {

    private TypedTableSelection $availableContests;
    private ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest, TypedTableSelection $availableContests) {
        parent::__construct($container);
        $this->contest = $contest;
        $this->availableContests = $availableContests;
    }

    protected function getTitle(): Title {
        return new Title($this->contest->name);
    }

    protected function getItems(): iterable {
        return $this->availableContests;
    }

    /**
     * @param ModelContest $item
     * @return bool
     */
    public function isItemActive($item): bool {
        return $this->contest->contest_id === $item->contest_id;
    }

    /**
     * @param ModelContest $item
     * @return Title
     */
    public function getItemTitle($item): Title {
        return new Title($item->name);
    }

    /**
     * @param ModelContest $item
     * @return string
     * @throws InvalidLinkException
     */
    public function getItemLink($item): string {
        return $this->getPresenter()->link('this', ['contestId' => $item->contest_id]);
    }
}
