<?php

namespace FKSDB\Components\Grids\Accommodation;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\ModelEventOrg;
use FKSDB\ORM\ModelEventParticipant;
use FKSDB\ORM\ModelEventPersonAccommodation;
use Nette\Utils\Html;
use ORM\Models\Events\ModelFyziklaniTeam;

abstract class BilletedGrid extends BaseGrid {
    /**
     * @var \ServiceEventPersonAccommodation
     */
    protected $serviceEventPersonAccommodation;

    function __construct(\ServiceEventPersonAccommodation $serviceEventPersonAccommodation) {
        parent::__construct();
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
    }

    /**
     * @param $presenter
     * @throws \Nette\Application\UI\InvalidLinkException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateGlobalButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addButton('confirmPayment')
            ->setClass('btn btn-sm btn-success')
            ->setText(_('Receive payment'))
            ->setLink(function ($row) {
                return $this->link('confirmPayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status !== ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addButton('deletePayment')
            ->setText(_('Delete payment'))
            ->setClass('btn btn-sm btn-warning')
            ->setLink(function ($row) {
                return $this->link('deletePayment!', $row->event_person_accommodation_id);
            })->setShow(function ($row) {
                return $row->status == ModelEventPersonAccommodation::STATUS_PAID;
            });

        $this->addGlobalButton('list', ['id' => null])
            ->setLabel(_('Zoznam ubytovaní'))
            ->setLink($this->getPresenter()->link('list'));

    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnPayment() {
        $this->addColumn('payment', _('Payment'))
            ->setRenderer(function ($row) {
                $model = ModelEventPersonAccommodation::createFromTableRow($row);
                $modelPayment = $model->getPayment();
                if (!$modelPayment) {
                    return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->add('No payment found');
                }
                return Html::el('span')->addAttributes(['class' => $modelPayment->getUIClass()])->add('#' . $modelPayment->getPaymentId() . '-' . $modelPayment->getStateLabel());
            })->setSortable(false);
    }

    protected function addColumnRole() {
        $this->addColumn('role', _('Role'))
            ->setRenderer(function ($row) {
                $container = Html::el('span');
                $model = ModelEventPersonAccommodation::createFromTableRow($row);
                $hasRole = false;
                $person = $model->getPerson();
                $eventId = $model->getEventAccommodation()->event_id;

                $teachers = $person->getEventTeacher()->where('event_id', $eventId);
                foreach ($teachers as $row) {
                    $hasRole = true;
                    $team = ModelFyziklaniTeam::createFromTableRow($row);
                    $container->add(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-primary'])
                        ->add(_('Teacher') . ' - ' . $team->name));
                }
                $eventOrgs = $person->getEventOrg()->where('event_id', $eventId);
                foreach ($eventOrgs as $row) {
                    $hasRole = true;
                    $org = ModelEventOrg::createFromTableRow($row);
                    $container->add(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-warning'])
                        ->add(_('Org') . ' - ' . $org->note));
                }

                $eventParticipants = $person->getEventParticipant()->where('event_id', $eventId);
                foreach ($eventParticipants as $row) {
                    $hasRole = true;
                    $participant = ModelEventParticipant::createFromTableRow($row);
                    $container->add(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-success'])
                        ->add(_('Participant') . ' - ' . _($participant->status)));
                }

                if (!$hasRole) {
                    $container->add(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-danger'])
                        ->add(_('No role')));
                }
                return $container;
            })->setSortable(false);
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnState() {
        $this->addColumn('status', _('State'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            $classNames = ($model->status === ModelEventPersonAccommodation::STATUS_PAID) ? 'badge badge-success' : 'badge badge-danger';
            return Html::el('span')
                ->addAttributes(['class' => $classNames])
                ->add((($model->status == ModelEventPersonAccommodation::STATUS_PAID) ? _('Paid') : _('Waiting')));
        });
    }

    /**
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function addColumnName() {
        $this->addColumn('name', _('Name'))->setRenderer(function ($row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            return $model->getPerson()->getFullName();
        });
    }


    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleConfirmPayment($id) {
        $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
        $model = ModelEventPersonAccommodation::createFromTableRow($row);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            $this->redirect('this');
            return;
        }
        $model->update(['status' => ModelEventPersonAccommodation::STATUS_PAID]);
        $this->serviceEventPersonAccommodation->save($model);
        $this->redirect('this');
    }

    /**
     * @param $id
     * @throws \Nette\Application\AbortException
     */
    public function handleDeletePayment($id) {
        $row = $this->serviceEventPersonAccommodation->findByPrimary($id);
        $model = ModelEventPersonAccommodation::createFromTableRow($row);
        if (!$model) {
            $this->flashMessage(_('some bullshit....'));
            $this->redirect('this');
            return;
        }
        $model->update(['status' => ModelEventPersonAccommodation::STATUS_WAITING_FOR_PAYMENT]);
        $this->serviceEventPersonAccommodation->save($model);
        $this->redirect('this');
    }
}
