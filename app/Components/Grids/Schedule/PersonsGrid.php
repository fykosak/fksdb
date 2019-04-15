<?php


namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class PersonsGrid extends BaseGrid {
    /**
     * @var ModelScheduleItem
     */
    private $item;

    /**
     * @param ModelScheduleItem $item
     */
    public function setItem(ModelScheduleItem $item) {
        $this->item = $item;
        $persons = $this->item->getInterested();
        $dataSource = new NDataSource($persons);
        $this->setDataSource($dataSource);
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));

        $this->addColumn('person', _('Person'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromTableRow($row);
            return $model->getPerson()->getFullName();
        })->setSortable(false);

        $this->addColumnRole();

        $this->addColumnPayment();

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromTableRow($row);
            return $model->state;
        });
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnPayment() {
        $this->addColumn('payment', _('Payment'))
            ->setRenderer(function ($row) {
                $model = ModelPersonSchedule::createFromTableRow($row);
                $modelPayment = $model->getPayment();
                if (!$modelPayment) {
                    return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText('No payment found');
                }
                return Html::el('span')->addAttributes(['class' => $modelPayment->getUIClass()])->addText('#' . $modelPayment->getPaymentId() . '-' . $modelPayment->getStateLabel());
            })->setSortable(false);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnRole() {
        $this->addColumn('role', _('Role'))
            ->setRenderer(function ($row) {
                $container = Html::el('span');
                $model = ModelPersonSchedule::createFromTableRow($row);
                $person = $model->getPerson();
                $roles = $person->getRolesForEvent($model->getScheduleItem()->getGroup()->getEvent());
                if (!\count($roles)) {
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-danger'])
                        ->addText(_('No role')));
                    return $container;
                }
                foreach ($roles as $role) {
                    switch ($role['type']) {
                        case 'teacher':
                            $container->addHtml(Html::el('span')
                                ->addAttributes(['class' => 'badge badge-9'])
                                ->addText(_('Teacher') . ' - ' . $role['team']->name));
                            break;
                        case'org':
                            $container->addHtml(Html::el('span')
                                ->addAttributes(['class' => 'badge badge-7'])
                                ->addText(_('Org') . ' - ' . $role['org']->note));
                            break;
                        case'participant':
                            $team = null;
                            /**
                             * @var ModelEventParticipant $participant
                             */
                            $participant = $role['participant'];
                            try {
                                $team = $participant->getFyziklaniTeam();
                            } catch (BadRequestException $exception) {
                            }
                            $container->addHtml(Html::el('span')
                                ->addAttributes(['class' => 'badge badge-10'])
                                ->addText(_('Participant') . ' - ' . _($participant->status) .
                                    ($team ? (' - team: ' . $team->name) : '')
                                ));
                    }
                }
                return $container;
            })->setSortable(false);
    }
}
