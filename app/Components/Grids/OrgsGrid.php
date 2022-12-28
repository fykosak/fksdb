<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

class OrgsGrid extends FilterBaseGrid
{

    private ContestModel $contest;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container);
        $this->contest = $contest;
    }

    protected function setData(): void
    {
        $this->data = $this->contest->getOrganisers();
    }

    protected function getFilterCallback(): void
    {
        $tokens = preg_split('/\s+/', $this->searchTerm['term']);
        foreach ($tokens as $token) {
            $this->data->where(
                'CONCAT(person.family_name, person.other_name, IFNULL(org.role,\'\'), IFNULL(org.contribution,\'\'))
                            LIKE CONCAT(\'%\', ? , \'%\')',
                $token
            );
        }
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->data->order('since DESC');

        $this->addColumns([
            'person.full_name',
            'org.since',
            'org.until',
            'org.role',
        ]);

        $this->addORMLink('org.edit', true);
        $this->addORMLink('org.detail', true);
    }
}
