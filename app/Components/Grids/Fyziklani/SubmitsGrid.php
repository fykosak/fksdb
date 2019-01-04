<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\model\Fyziklani\TaskCodePreprocessor;
use FKSDB\ORM\ModelEvent;
use FyziklaniModule\BasePresenter;
use Nette\Database\Table\Selection;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniSubmit;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class SubmitsGrid extends BaseGrid {

    /**
     *
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->event = $event;
        parent::__construct();
    }

    /**
     * @param BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('Id týmu'));
        $this->addColumn('label', _('Úloha'));
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Místnost'));
        $this->addColumn('modified', _('Zadané'));
        $this->addButton('edit', null)->setClass('btn btn-sm btn-warning')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upravit'))->setShow(function (\ModelFyziklaniSubmit $row) {
            return $row->getTeam()->hasOpenSubmit() && !is_null($row->points);
        });

        $this->addButton('delete', null)->setClass('btn btn-sm btn-danger')->setLink(function ($row) {
            return $this->link('delete!', $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _('Opravdu vzít submit úlohy zpět?');
        })->setText(_('Smazat'))->setShow(function (\ModelFyziklaniSubmit $row) {

            return $row->getTeam()->hasOpenSubmit() && !is_null($row->points);
        });

        $submits = $this->serviceFyziklaniSubmit->findAll($this->event)
            ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name,e_fyziklani_team_id.room');
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($dataSource);
    }

    private function getFilterCallBack() {
        return function (Selection $table, $value) {
            $l = strlen($value);
            $code = str_repeat('0', 9 - $l) . strtoupper($value);
            if (TaskCodePreprocessor::checkControlNumber($code)) {
                $taskLabel = TaskCodePreprocessor::extractTaskLabel($code);
                $teamId = TaskCodePreprocessor::extractTeamId($code);
                $table->where('e_fyziklani_team_id.e_fyziklani_team_id =? AND fyziklani_task.label =? ', $teamId, $taskLabel);

            } else {
                $tokens = preg_split('/\s+/', $value);
                foreach ($tokens as $token) {
                    $table->where('e_fyziklani_team_id.name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task.label LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
                }
            }
        };
    }

    public function handleDelete($id) {
        /**
         * @var $submit \ModelFyziklaniSubmit
         */
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($id);
        $teamId = $submit->e_fyziklani_team_id;
        if (!$teamId) {
            $this->flashMessage(_('Submit neexistuje'), 'danger');
            return;
        }
        if (!$submit->getTeam()->hasOpenSubmit()) {

            $this->flashMessage('Tento tým má už uzavřené bodování', 'warning');
            return;
        }
        $submit = $this->serviceFyziklaniSubmit->findByPrimary($id);
        $this->serviceFyziklaniSubmit->updateModel($submit, [
            'points' => 0,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->flashMessage(_('Počet bodov bol upravený na 0, pre opätovné zadanie prosím použite funciu upraviť submit.'), 'success');
    }
}
