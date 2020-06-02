<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class OrgsGrid extends BaseGrid {

    private ServiceOrg $serviceOrg;

    private ModelContest $contest;

    /**
     * OrgsGrid constructor.
     * @param Container $container
     * @param ModelContest $contest
     */
    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container);
        $this->contest = $contest;
    }

    public function injectServiceOrg(ServiceOrg $serviceOrg): void {
        $this->serviceOrg = $serviceOrg;
    }

    protected function getData(): IDataSource {
        $orgs = $this->serviceOrg->getTable()->where('contest_id', $this->contest->contest_id)
            ->select('org.*, person.family_name AS display_name');

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
     * @param Presenter|\IContestPresenter $presenter
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->setDataSource($this->getData());
        $this->setDefaultOrder('since DESC');

        $this->addColumns([
            'person.full_name',
            'org.since',
            'org.until',
            'org.role',
        ]);

        $this->addLink('org.edit', true);
        $this->addLink('org.detail', true);

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel(_('Založit organizátora'))
                ->setLink($this->getPresenter()->link('create'));
        }
    }

    protected function getModelClassName(): string {
        return ModelOrg::class;
    }
}
