<?php

namespace FKSDB\Components\Grids\Events;

use FKSDB\Components\Grids\BaseGrid;
use Nette\Database\Table\Selection;
use ServiceEvent;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class EventsGrid extends BaseGrid {

    /**
     *
     * @var ServiceEvent
     */
    private $serviceEvent;

    function __construct(ServiceEvent $serviceEvent) {
        $this->serviceEvent = $serviceEvent;
    }

    protected function configure($presenter) {
        parent::configure($presenter);

        //
        // data
        //
        
        $events = $this->serviceEvent->getEvents($presenter->getSelectedContest(), $presenter->getSelectedYear());

        $dataSource = new SearchableDataSource($events);
        $dataSource->setFilterCallback(function(Selection $table, $value) {
                    $tokens = preg_split('/\s+/', $value);
                    foreach ($tokens as $token) {
                        $table->where('event.name LIKE CONCAT(\'%\', ? , \'%\') OR event_type.name LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
                    }
                });
        $this->setDefaultOrder('event.begin ASC');
        $this->setDataSource($dataSource);

        //
        // columns
        //
        $this->addColumn('event_id', _('ID akce'));
        $this->addColumn('type_name', _('Typ akce'));
        $this->addColumn('name', _('Název'));
        $this->addColumn('year', _('Ročník semináře'));

        //
        // operations
        //
        $that = $this;
        $this->addButton('model', _('Model'))
                ->setText('Model') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("model", $row->event_id);
                        });
        $this->addButton('edit', _('Upravit'))
                ->setText('Upravit') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link("edit", $row->event_id);
                        });
        $this->addButton('applications')
                ->setText('Přihlášky') //todo i18n
                ->setLink(function($row) use ($that) {
                            return $that->getPresenter()->link('applications', $row->event_id);
                        });
        $this->addGlobalButton('add')
                ->setLink($this->getPresenter()->link('create'))
                ->setLabel('Přidat akci')
                ->setClass('btn btn-sm btn-primary');

        //
        // appeareance
    //

    }

}
