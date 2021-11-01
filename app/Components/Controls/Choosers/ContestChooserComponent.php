<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Choosers;

use FKSDB\Models\ORM\Models\ModelContest;
use Fykosak\NetteORM\TypedTableSelection;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class ContestChooserComponent extends ChooserComponent
{

    private TypedTableSelection $availableContests;
    private ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest, TypedTableSelection $availableContests)
    {
        parent::__construct($container);
        $this->contest = $contest;
        $this->availableContests = $availableContests;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        /** @var ModelContest $contest */
        foreach ($this->availableContests as $contest) {
            $items[] = new NavItem(
                new Title($contest->name),
                'this',
                ['contestId' => $contest->contest_id],
                [],
                $contest->contest_id === $this->contest->contest_id
            );
        }
        return new NavItem(new Title($this->contest->name), '#', [], $items);
    }
}
