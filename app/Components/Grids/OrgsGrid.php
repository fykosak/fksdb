<?php

namespace FKSDB\Components\Grids;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;
use OrgModule\OrgPresenter;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class OrgsGrid extends BaseGrid {

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;
    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * OrgsGrid constructor.
     * @param Container $container
     * @param ModelContest $contest
     */
    public function __construct(Container $container, ModelContest $contest) {
        parent::__construct($container);
        $this->contest = $contest;
    }

    /**
     * @param ServiceOrg $serviceOrg
     * @return void
     */
    public function injectServiceOrg(ServiceOrg $serviceOrg) {
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
     * @param OrgPresenter $presenter
     * @throws BadRequestException
     * @throws InvalidLinkException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->setDefaultOrder('since DESC');

        $this->addColumns([
            'referenced.person_name',
            DbNames::TAB_ORG . '.since',
            DbNames::TAB_ORG . '.until',
            DbNames::TAB_ORG . '.role',
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
