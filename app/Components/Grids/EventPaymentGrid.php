<?php

namespace FKSDB\Components\Grids;


use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\TransitionsFactory;
use FKSDB\EventPayment\Transition\UnavailableTransitionException;
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
    /**
     * @var Machine
     */
    private $machine;

    public function __construct(Machine $machine, \ServiceEventPayment $servicePayment, TransitionsFactory $transitionFactory, $eventId) {
        parent::__construct();
        $this->eventId = $eventId;
        $this->serviceEventPayment = $servicePayment;
        $this->transitionFactory = $transitionFactory;
        $this->machine = $machine;
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
        $this->addColumn('id', _('#'))->setRenderer(function ($row) {
            return '#' . ModelEventPayment::createFromTableRow($row)->getPaymentId();
        });
        $this->addColumn('person_name', _('Person'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getFullName();
        });
        $this->addColumn('person_email', _('e-mail'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getInfo()->email;
        });
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            return $row->price_kc . ' Kč/' . $row->price_eur . ' €';
        });
        $this->addColumn('constant_symbol', _('CS'));
        $this->addColumn('variable_symbol', _('VS'));
        $this->addColumn('specific_symbol', _('SS'));
        $this->addColumn('bank_account', _('Bank acc.'));
        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $class = ModelEventPayment::createFromTableRow($row)->getUIClass();
            return Html::el('span')->addAttributes(['class' => $class])->add(_($row->state));
        });

        $this->addColumn('tr', _('Actions'))->setRenderer(function ($row) {
            $model = ModelEventPayment::createFromTableRow($row);
            $container = Html::el('span')->addAttributes(['class' => 'btn-group']);
            foreach ($this->machine->getAvailableTransitions($model->state, true) as $transition) {
                $container->add(Html::el('a')->addAttributes([
                    'href' => $this->link('transition', [
                            'id' => $model->payment_id,
                            'transition' => $transition->getId(),
                        ]
                    ),
                    'class' => 'btn btn-sm btn-' . $transition->getType(),
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

    public function handleTransition(int $id, string $transition) {
        $row = $this->serviceEventPayment->findByPrimary($id);
        if (!$row) {
            $this->flashMessage('Payment doesn\'t exists.');
            return;
        }
        $model = ModelEventPayment::createFromTableRow($row);
        try {
            $model->executeTransition($this->machine, $transition, true);
            $this->flashMessage('Prechod vykonaný');
            $this->redirect('this');
        } catch (UnavailableTransitionException $e) {
            Debugger::log($e);
            Debugger::barDump($e);
            $this->flashMessage('Some error...', 'danger');
        }
    }
}
