<?php

namespace FKSDB\Components\Grids;

use Nette\Database\Table\Selection;
use ServiceEventOrg;
use SQL\SearchableDataSource;


class EventOrgsGrid extends BaseGrid {

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    private $event_id;

    function __construct($event_id, ServiceEventOrg $serviceEventOrg) {
        parent::__construct();
        $this->event_id = $event_id;
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $orgs = $this->serviceEventOrg->findByEventID($this->event_id);

        $dataSource = new SearchableDataSource($orgs);
        $this->setDataSource($dataSource);
        $this->addColumn('display_name', _('Jméno'))->setRenderer(function ($row) {
            $person = $row->getPerson();
            return $person->getFullname();
        });
        $this->addColumn('note', _('Poznámka'));
        $this->addButton('edit', _('Upravit'))->setText('Upravit')//todo i18n
        ->setLink(function ($row) {
            return $this->getPresenter()->link('edit', $row->e_org_id);
        })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('edit', ['id' => $row->e_org_id]);
            });
        $this->addButton('delete', _('Smazat'))->setClass('btn btn-sm btn-danger')->setText('Smazat')//todo i18n
        ->setLink(function ($row) {
            return $this->getPresenter()->link('delete', $row->e_org_id);
        })
            ->setShow(function ($row) use ($presenter) {
                return $presenter->authorized('delete', ['id' => $row->e_org_id]);
            })
            ->setConfirmationDialog(function () {
                return _('Opravdu smazat organizátora?'); //todo i18n
            });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                ->setLabel('Přidat organizátora')
                ->setLink($this->getPresenter()->link('create'));
        }

    }

}
