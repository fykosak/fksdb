<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

class OrgsGrid extends FilterBaseGrid
{

    private ContestModel $contest;

    public function __construct(Container $container, ContestModel $contest)
    {
        parent::__construct($container);
        $this->contest = $contest;
    }

    protected function getData(): SearchableDataSource
    {
        $dataSource = new SearchableDataSource($this->contest->getOrganisers());
        $dataSource->setFilterCallback(function (Selection $table, array $value) {
            $tokens = preg_split('/\s+/', $value['term']);
            foreach ($tokens as $token) {
                $table->where(
                    'CONCAT(person.family_name, person.other_name, IFNULL(org.role,\'\'), IFNULL(org.contribution,\'\'))
                            LIKE CONCAT(\'%\', ? , \'%\')',
                    $token
                );
            }
        });
        return $dataSource;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->setDefaultOrder('since DESC');

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
