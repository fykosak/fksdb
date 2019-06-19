<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use NiftyGrid\DataSource\NDataSource;

/**
 * Class MyApplicationsGrid
 * @package FKSDB\Components\Grids\Events\Application
 */
class MyApplicationsGrid extends BaseGrid {
    /**
     * @var ModelPerson
     */
    private $person;
    /**
     * @var ModelContest
     */
    private $contest;

    /**
     * MyApplicationsGrid constructor.
     * @param ModelContest $contest
     * @param ModelPerson $person
     * @param TableReflectionFactory|null $tableReflectionFactory
     */
    public function __construct(ModelContest $contest, ModelPerson $person, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->person = $person;
        $this->contest = $contest;
    }

    /**
     * @param Presenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;
        $source = $this->person->getEventParticipant();
        $source->where('event.event_type.contest_id', $this->contest->contest_id);
        //$source->select('event_participant.*,event.*');
        $source = new NDataSource($source);
        $this->setDataSource($source);
        $eventCallBack = function (ActiveRow $row) {
            return ModelEventParticipant::createFromTableRow($row)->getEvent();
        };
        $this->addJoinedColumn(DbNames::TAB_EVENT, 'name', $eventCallBack);
        $this->addJoinedColumn(DbNames::TAB_EVENT, 'year', $eventCallBack);
        $this->addJoinedColumn(DbNames::TAB_EVENT, 'event_year', $eventCallBack);
        $this->addReflectionColumn(DbNames::TAB_EVENT_PARTICIPANT, 'status', ModelEventParticipant::class);

    }
}
