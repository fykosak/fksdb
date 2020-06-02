<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class MyApplicationsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 * TODO!!!
 */
class MyApplicationsGrid extends BaseGrid {

    private ModelPerson $person;

    private ModelContest $contest;

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
     * @return void
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->paginate = false;
        $source = $this->person->getEventParticipant();
        $source->where('event.event_type.contest_id', $this->contest->contest_id);
        //$source->select('event_participant.*,event.*');
        $source = new NDataSource($source);
        $this->setDataSource($source);
        /*   $eventCallBack = function (ActiveRow $row) {
               return ModelEventParticipant::createFromActiveRow($row)->getEvent();
           };*/

        //     $this->addJoinedColumn(DbNames::TAB_EVENT, 'name', $eventCallBack);
        //   $this->addJoinedColumn(DbNames::TAB_EVENT, 'year', $eventCallBack);
        //   $this->addJoinedColumn(DbNames::TAB_EVENT, 'event_year', $eventCallBack);
        $this->addColumns(['event_participant.status']);
    }

    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }
}
