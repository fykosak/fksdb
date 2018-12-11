<?php

namespace FKSDB\Components\Grids\EventPayment;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\EventPayment\PriceCalculator\Price;
use FKSDB\EventPayment\Transition\Machine;
use FKSDB\EventPayment\Transition\UnavailableTransitionException;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;
use FKSDB\ORM\ModelPerson;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Mišo <miso@fykoscz>
 */
class OrgEventPaymentGrid extends BaseGrid {

    /**
     * @var \ServicePayment
     */
    private $serviceEventPayment;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var Machine
     */
    private $machine;

    public function __construct(Machine $machine, \ServicePayment $servicePayment, ModelEvent $event) {
        parent::__construct();
        $this->event = $event;
        $this->serviceEventPayment = $servicePayment;
        $this->machine = $machine;
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        //
        // data
        //
        $schools = $this->serviceEventPayment->where('event_id', $this->event->event_id);

        $dataSource = new NDataSource($schools);
        $this->setDataSource($dataSource);

        //
        // columns
        //
        $this->addColumn('id', '#')->setRenderer(function ($row) {
            return '#' . ModelPayment::createFromTableRow($row)->getPaymentId();
        });
        $this->addColumn('person_name', _('Person'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getFullName();
        });
        $this->addColumn('person_email', _('e-mail'))->setRenderer(function ($row) {
            return ModelPerson::createFromTableRow($row->person)->getInfo()->email;
        });
        $this->addColumn('price', _('Price'))->setRenderer(function ($row) {
            $model = ModelPayment::createFromTableRow($row);
            return $model->price . ' ' . Price::getLabel($model->currency);
        });
        $this->addColumn('constant_symbol', _('CS'));
        $this->addColumn('variable_symbol', _('VS'));
        $this->addColumn('specific_symbol', _('SS'));
        $this->addColumn('bank_account', _('Bank acc.'));
        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPayment::createFromTableRow($row);
            return Html::el('span')->addAttributes(['class' => $model->getUIClass()])->add(_($model->state));
        });

        $this->addColumn('tr', _('Actions'))->setRenderer(function ($row) {
            $model = ModelPayment::createFromTableRow($row);
            $container = Html::el('span')->addAttributes(['class' => 'btn-group']);
            foreach ($this->machine->getAvailableTransitions($model) as $transition) {
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
            ->setText(_('Edit'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('edit', $row->payment_id);
            });
    }

    public function handleTransition(int $id, string $transition) {
        $row = $this->serviceEventPayment->findByPrimary($id);
        if (!$row) {
            $this->flashMessage(_('Payment doesnt exists.'));
            return;
        }
        $model = ModelPayment::createFromTableRow($row);
        try {
            $this->machine->executeTransition($transition, $model);
            $this->flashMessage(_('Prechod vykonaný'), 'success');
            $this->redirect('this');
        } catch (UnavailableTransitionException $e) {
            Debugger::log($e);
            $this->flashMessage($e->getMessage(), 'danger');
        }
    }
}
