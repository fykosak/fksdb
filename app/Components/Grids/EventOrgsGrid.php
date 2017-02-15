<?php

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\BaseGrid;
use ModelEvent;
use ServiceEventOrg;
use SQL\SearchableDataSource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class EventOrgsGrid extends BaseGrid {

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    /**
     * @var ModelEvent
     */
    private $event;

    function __construct(ModelEvent $event, ServiceEventOrg $serviceEventOrg) {
        parent::__construct();
        $this->event = $event;
        $this->serviceEventOrg = $serviceEventOrg;
    }

    protected function configure($presenter) {
        parent::configure($presenter);


        $orgs = $this->serviceEventOrg->findByEventID($this->event->getPrimary());

        $dataSource = new SearchableDataSource($orgs);
        $this->setDataSource($dataSource);
        $this->addColumn('display_name', _('Jméno'))->setRenderer(function ($row) {
                    $person = $row->getPerson();
                    return $person->getFullname();
                });
        $this->addColumn('note', _('Poznámka'));
        $that = $this;
        $this->addButton("edit", _("Upravit"))->setText('Upravit')//todo i18n
                ->setLink(function ($row) use ($that) {
                            return $that->getPresenter()->link('edit', $row->e_org_id);
                        })
                ->setShow(function($row) use ($presenter) {
                            return $presenter->authorized("edit", ['id' => $row->e_org_id]);
                        });
        $this->addButton("delete", _("Smazat"))->setClass('btn btn-xs btn-danger')->setText('Smazat')//todo i18n
                ->setLink(function ($row) use ($that) {
                            return $that->getPresenter()->link("delete", $row->e_org_id);
                        })
                ->setShow(function($row) use ($presenter) {
                            return $presenter->authorized("delete", ['id' => $row->e_org_id]);
                        })
                ->setConfirmationDialog(function () {
                            return _("Opravdu smazat organizátora?"); //todo i18n
                        });

        if ($presenter->authorized('create')) {
            $this->addGlobalButton('add')
                    ->setLabel('Přidat organizátora')
                    ->setLink($this->getPresenter()->link('create'));
        }
    }

}