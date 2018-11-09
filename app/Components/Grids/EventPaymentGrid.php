<?php

namespace FKSDB\Components\Grids;


use Events\Payment\MachineFactory;
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

    private $transitionFactory;

    public function __construct(\ServiceEventPayment $servicePayment, MachineFactory $transitionFactory, $eventId) {
        Debugger::barDump($eventId);
        parent::__construct();
        $this->eventId = $eventId;
        $this->serviceEventPayment = $servicePayment;
        $this->transitionFactory = $transitionFactory;
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
        $this->addColumn('person_email', _('e-mail'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getInfo()->email;
        });
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            return $row->price_kc . ' Kč/' . $row->price_eur . ' €';
        });
        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
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
        $this->addColumn('tr', _('Akcie'))->setRenderer(function ($row) {
            $model = ModelEventPayment::createFromTableRow($row);
            $machine = $model->createMachine($this->transitionFactory);
            $machine->setState($row->state);
            $container = Html::el('span');
            foreach ($machine->getAvailableTransitions() as $transition) {
                $container->add(Html::el('a')->addAttributes([
                    'href' => $this->link('transition', [
                            'id' => $row->payment_id,
                            'transition' => $transition->getId(),
                        ]
                    ),
                    'class' => $transition->isDangerous() ? 'btn btn-danger' : 'btn btn-secondary',
                ])->add($transition->getLabel()));
            }
            return $container;
        });

        //
        // operations
        //
        $this->addButton('edit', _('Edit'))
            ->setText('Edit')
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->payment_id);
            });
    }

    public function handleTransition($id, $transition) {
        $row = $this->serviceEventPayment->findByPrimary($id);
        if (!$row) {
            $this->flashMessage('Payment doesn\'t exists.');
            return;
        }
        $model = ModelEventPayment::createFromTableRow($row);
        try {
            $model->executeTransition($transition, $this->transitionFactory);
        } catch (\Exception $e) {
            Debugger::log($e);
            $this->flashMessage('Some error...', 'danger');
        } finally {
           // $this->serviceEventPayment->save($model);
            $this->flashMessage('Prechod vykonaný');
         //   $this->redirect('this');
        }


    }

}
