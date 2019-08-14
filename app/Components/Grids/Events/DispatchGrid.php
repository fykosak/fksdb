<?php


namespace FKSDB\Components\Grids\Events;


use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\YearCalculator;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use function array_key_exists;

/**
 * Class DispatchGrid
 * @package FKSDB\Components\Grids\Events
 */
class DispatchGrid extends BaseGrid {

    /**
     * @var ServiceEvent
     */
    private $serviceEvent;
    /**
     * @var ModelPerson
     */
    private $person;
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * DispatchGrid constructor.
     * @param ServiceEvent $serviceEvent
     * @param ModelPerson $person
     * @param YearCalculator $yearCalculator
     */
    function __construct(ServiceEvent $serviceEvent, ModelPerson $person, YearCalculator $yearCalculator) {
        parent::__construct();
        $this->person = $person;
        $this->serviceEvent = $serviceEvent;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $events = $this->serviceEvent->getTable();

        $dataSource = new NDataSource($events);

        $this->setDataSource($dataSource);
        $this->setDefaultOrder('begin DESC');


        //
        // columns
        //
        $this->addColumn('event_id', _('Event Id'))->setRenderer(function ($row) {
            return '#' . $row->event_id;
        });
        $this->addColumn('name', _('Name'));
        $this->addColumn('contest_id', _('Contest'))->setRenderer(function ($row) {
            return $row->event_type->contest->name;
        })->setSortable(false);
        $this->addColumn('year', _('Year'));
        $this->addColumn('roles', _('Roles'))->setRenderer(function ($row) {
            $container = Html::el('span');
            $modelEvent = ModelEvent::createFromActiveRow($row);
            $isEventParticipant = $this->person->isEventParticipant($modelEvent->event_id);
            if ($isEventParticipant) {
                $container->addHtml(Html::el('span')->addAttributes(['class' => 'badge badge-success'])->addText(_('Event participant')));
            }
            $isEventOrg = count($this->person->getEventOrg()->where('event_id', $modelEvent->event_id));
            if ($isEventOrg) {
                $container->addHtml(Html::el('span')->addAttributes(['class' => 'badge badge-info'])->addText(_('Event org')));
            }
            $isOrg = array_key_exists($modelEvent->getEventType()->contest_id, $this->person->getActiveOrgs($this->yearCalculator));
            if ($isOrg) {
                $container->addHtml(Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Contest org')));
            }
            return $container;
        })->setSortable(false);

        //
        // operations
        //

        $this->addButton('detail', _('Detail'))
            ->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('Dashboard:default', [
                    'eventId' => $row->event_id,
                ]);
            })->setClass('btn btn-sm btn-primary');
    }
}
