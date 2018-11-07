<?php

namespace FKSDB\Components\Grids;


use FKSDB\ORM\ModelEventPayment;
use FKSDB\ORM\ModelPerson;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class EventPaymentGrid extends BaseGrid {

    /**
     * @var \ServiceEventPayment
     */
    private $serviceEventPayment;
    /**
     * @var integer
     */
    private $eventId;

    public function __construct(\ServiceEventPayment $servicePayment, $eventId) {
        Debugger::barDump($eventId);
        parent::__construct();
        $this->eventId = $eventId;
        $this->serviceEventPayment = $servicePayment;
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $schools = $this->serviceEventPayment->where('event_id', $this->eventId);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

        //
        // columns
        //
        $this->addColumn('constant_symbol', _('CS'));
        $this->addColumn('person_name', _('Osoba'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getFullName();
        });
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            return $row->price_kc . ' Kč/' . $row->price_eur . ' €';
        });
        $this->addColumn('state', _('Staus'))->setRenderer(function ($row) {
            $class = 'badge ';
            switch ($row->state) {
                case ModelEventPayment::STATE_WAITING:
                    $class .= 'badge-warning';
                    break;
                case ModelEventPayment::STATE_CANCELED:
                    $class .= 'badge-secondary';
                    break;
                case ModelEventPayment::STATE_CONFIRMED:
                    $class .= 'badge-success';
                    break;
                default:
                    throw new \Exception('Fuck this shit');
            }
            return Html::el('span')->addAttributes(['class' => $class])->add(_($row->state));
        });
        $this->addColumn('tr', _('TR'))->setRenderer(function ($row) {
            $model = ModelEventPayment::createFromTableRow($row);
            $machine = $model->createMachine();
            Debugger::barDump($this->presenter->getContext()->getParameters());
            $machine->setState($row->state);
            Debugger::barDump($machine);
            Debugger::barDump($machine->getAvailableTransitions());

            return null;
        });
        //
        // operations
        //
        $this->addButton("edit", _("Upravit"))
            ->setText('Upravit')//todo i18n
            ->setLink(function ($row) {
                return $this->getPresenter()->link("edit", $row->payment_id);
            });
    }

}
