<?php

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelOrg;
use Nette\Application\UI\Presenter;
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
class OrgsGrid extends BaseGrid {

    private ModelContest $contest;

    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container);
        $this->contest = $contest;
    }

    protected function getData(): IDataSource {
        $orgs = $this->contest->related(DbNames::TAB_ORG);

        $dataSource = new SearchableDataSource($orgs);
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
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->setDefaultOrder('since DESC');

        $this->addColumns([
            'person.full_name',
            'org.since',
            'org.until',
            'org.role',
        ]);

        $this->addLink('org.edit', true);
        $this->addLink('org.detail', true);
    }

    protected function getModelClassName(): string {
        return ModelOrg::class;
    }
}
