<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use Nette\Application\BadRequestException;
use Nette\Database\Table\Selection;
use OrgModule\OrgPresenter;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class OrgsGrid extends BaseGrid {

    /**
     * @var \FKSDB\ORM\Services\ServiceOrg
     */
    private $serviceOrg;

    /**
     * OrgsGrid constructor.
     * @param \FKSDB\ORM\Services\ServiceOrg $serviceOrg
     * @param TableReflectionFactory $tableReflectionFactory
     */
    function __construct(ServiceOrg $serviceOrg, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);

        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param OrgPresenter $presenter
     * @throws BadRequestException
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
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

        foreach (['person_id', 'since', 'until', 'role'] as $field) {
            $this->addReflectionColumn(DbNames::TAB_ORG, $field, ModelOrg::class);
        }

        //
        // operations
        //
        $this->addButton('edit', _('Edit'))
            ->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->org_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('edit', ['id' => $row->org_id]);
            });

        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('detail', $row->org_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('detail', ['id' => $row->org_id]);
            });


        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel(_('Založit organizátora'))
                ->setLink($this->getPresenter()->link('create'));
        }
    }
}
