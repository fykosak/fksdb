<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use Nette\Application\BadRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Database\Table\Selection;
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
     * OrgsGrid constructor.
     * @param ServiceOrg $serviceOrg
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceOrg $serviceOrg, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);

        $this->serviceOrg = $serviceOrg;
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
        //
        // data
        //
        $orgs = $this->serviceOrg->getTable()->where('contest_id', $presenter->getSelectedContest()->contest_id)
            ->select('org.*, person.family_name AS display_name');

        $dataSource = new SearchableDataSource($orgs);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('CONCAT(person.family_name, person.other_name, IFNULL(org.role,\'\'), IFNULL(org.contribution,\'\'))
                            LIKE CONCAT(\'%\', ? , \'%\')', $token);
            }
        });
        $this->setDataSource($dataSource);
        $this->setDefaultOrder('since DESC');

        $this->addReflectionColumn('referenced', 'person_name', ModelOrg::class);

        foreach (['since', 'until', 'role'] as $field) {
            $this->addReflectionColumn(DbNames::TAB_ORG, $field, ModelOrg::class);
        }

        $this->addLinkButton($presenter, 'edit', 'edit', _('Edit'), true);
        $this->addLinkButton($presenter, 'detail', 'detail', _('Detail'), true);

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel(_('Založit organizátora'))
                ->setLink($this->getPresenter()->link('create'));
        }
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelOrg::class;
    }
}
