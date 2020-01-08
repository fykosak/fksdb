<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\DatabaseReflection\ValuePrinters\EventRole;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\YearCalculator;
use Nette\Utils\Html;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class PersonsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class PersonsGrid extends BaseGrid {
    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    /**
     * PersonsGrid constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param YearCalculator $yearCalculator
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, YearCalculator $yearCalculator) {
        $this->yearCalculator = $yearCalculator;
        parent::__construct($tableReflectionFactory);
    }

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
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getPerson()->getFullName();
        })->setSortable(false);

        $this->addColumnRole();

        $this->addColumnPayment();

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnPayment() {
        $this->addColumn('payment', _('Payment'))
            ->setRenderer(function ($row) {
                $model = ModelPersonSchedule::createFromActiveRow($row);
                $modelPayment = $model->getPayment();
                if (!$modelPayment) {
                    return Html::el('span')->addAttributes(['class' => 'badge badge-danger'])->addText('No payment found');
                }
                // TODO
                return Html::el('span')->addAttributes(['class' => ''])->addText('#' . $modelPayment->getPaymentId() . '-');
            })->setSortable(false);
    }

    /**
     * @throws DuplicateColumnException
     */
    protected function addColumnRole() {
        $this->addColumn('role', _('Role'))
            ->setRenderer(function ($row) {
                $container = Html::el('span');
                $model = ModelPersonSchedule::createFromActiveRow($row);
                $person = $model->getPerson();
                $roles = $person->getRolesForEvent($model->getScheduleItem()->getGroup()->getEvent(), $this->yearCalculator);
                if (!\count($roles)) {
                    $container->addHtml(Html::el('span')
                        ->addAttributes(['class' => 'badge badge-danger'])
                        ->addText(_('No role')));
                    return $container;
                }
                return EventRole::getHtml($roles);
            })->setSortable(false);
    }
}
