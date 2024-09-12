<?php

declare(strict_types=1);

namespace FKSDB\Components\Choosers;

use FKSDB\Models\ORM\Models\ContestModel;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

final class ContestChooserComponent extends ChooserComponent
{
    /** @phpstan-var TypedSelection<ContestModel> */
    private TypedSelection $availableContests;
    private ContestModel $contest;

    /**
     * @phpstan-param TypedSelection<ContestModel> $availableContests
     */
    public function __construct(Container $container, ContestModel $contest, TypedSelection $availableContests)
    {
        parent::__construct($container);
        $this->contest = $contest;
        $this->availableContests = $availableContests;
    }

    protected function getItem(): NavItem
    {
        $items = [];
        /** @var ContestModel $contest */
        foreach ($this->availableContests as $contest) {
            $items[] = new NavItem(
                new Title(null, $contest->name),
                'this',
                ['contestId' => $contest->contest_id],
                [],
                $contest->contest_id === $this->contest->contest_id
            );
        }
        return new NavItem(new Title(null, $this->contest->name), '#', [], $items);
    }
}
