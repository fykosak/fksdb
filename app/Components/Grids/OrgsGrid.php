<?php

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Services\ServiceOrg;
use Nette\Application\IPresenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use FKSDB\Models\SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class OrgsGrid extends EntityGrid {

    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container, ServiceOrg::class, ['person.full_name',
            'org.since',
            'org.until',
            'org.role',
        ], [
            'contest_id' => $contest->contest_id,
        ]);
    }

    protected function createDataSource(Selection $source): IDataSource {
        return new SearchableDataSource($source);
    }

    protected function getData(): IDataSource {
        $dataSource = parent::getData();
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('CONCAT(person.family_name, person.other_name, IFNULL(org.role,\'\'), IFNULL(org.contribution,\'\'))
                            LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        return $dataSource;
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);

        $this->setDefaultOrder('since DESC');

        $this->addLink('org.edit', true);
        $this->addLink('org.detail', true);
    }
}
