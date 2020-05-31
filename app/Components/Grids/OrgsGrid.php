<?php

namespace FKSDB\Components\Grids;

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

    private ServiceOrg $serviceOrg;

    public function injectServiceOrg(ServiceOrg $serviceOrg): void {
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
