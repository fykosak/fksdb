<?php

namespace FKSDB\Components\Grids;

use Nette\Database\Table\Selection;
use Nette\Utils\Html;
use ServiceContestant;
use ServiceOrg;
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

    function __construct(ServiceOrg $serviceOrg) {
        parent::__construct();

        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.v4.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . 'BaseGrid.paginator.v4.latte');
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

        //
        // columns
        //
        $this->addColumn('display_name', _('Jméno'))->setRenderer(function ($row) {
            $person = $row->getPerson();
            return $person->getFullname();
        });
        $this->addColumn('since', _('Začal'));
        $this->addColumn('until', _('Skončil'))->setRenderer(function ($row) {
            return ($row->until === NULL) ? Html::el('span')->addAttributes(['class' => 'badge badge-success'])->add(_('Still organizes')) : $row->until;
        });
        $this->addColumn('role', _('Funkce'));

        //
        // operations
        //
        $this->addButton("edit", _("Upravit"))
            ->setText('Upravit')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link("edit", $row->org_id);
            })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized("edit", array('id' => $row->org_id));
            });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel('Založit organizátora')
                ->setLink($this->getPresenter()->link('create'));
        }


        //
        // appeareance
        //

    }

}
