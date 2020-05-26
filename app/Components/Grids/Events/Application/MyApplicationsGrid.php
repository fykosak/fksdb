<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class MyApplicationsGrid
 * *
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
     * @param Container $container
     */
    public function __construct(ModelContest $contest, ModelPerson $person, Container $container) {
        parent::__construct($container);
        $this->person = $person;
        $this->contest = $contest;
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateColumnException
     * @throws NotImplementedException
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
            return ModelEventParticipant::createFromActiveRow($row)->getEvent();
        };

        //     $this->addJoinedColumn(DbNames::TAB_EVENT, 'name', $eventCallBack);
        //   $this->addJoinedColumn(DbNames::TAB_EVENT, 'year', $eventCallBack);
        //   $this->addJoinedColumn(DbNames::TAB_EVENT, 'event_year', $eventCallBack);
        $this->addColumns([DbNames::TAB_EVENT_PARTICIPANT . '.status']);
    }

    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }
}
