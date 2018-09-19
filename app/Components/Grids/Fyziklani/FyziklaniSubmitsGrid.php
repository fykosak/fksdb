<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
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
class FyziklaniSubmitsGrid extends BaseGrid {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     *
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @var integer
     */
    private $eventID;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param integer $eventID
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct($eventID, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->eventID = $eventID;
        parent::__construct();
    }

    /**
     * @param $presenter BasePresenter
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . '../BaseGrid.v4.latte');
        $this['paginator']->setTemplate(__DIR__ . DIRECTORY_SEPARATOR . '../BaseGrid.paginator.v4.latte');

        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('ID týmu'));
        $this->addColumn('label', _('Úloha'));
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Místnost'));
        $this->addColumn('modified', _('Zadané'));
        $this->addButton('edit', null)->setClass('btn btn-xs btn-default')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upravit'))->setShow(function (\ModelFyziklaniSubmit $row) {
            return $row->getTeam()->hasOpenSubmit() && !is_null($row->points);
        });

        $this->addButton('delete', null)->setClass('btn btn-xs btn-danger')->setLink(function ($row) {
            return $this->link("delete!", $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _("Opravdu vzít submit úlohy zpět?"); //todo i18n
        })->setText(_('Smazat'))->setShow(function (\ModelFyziklaniSubmit $row) {

            return $row->getTeam()->hasOpenSubmit() && !is_null($row->points);
        });

        $submits = $this->serviceFyziklaniSubmit->findAll($this->eventID)
            ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name,e_fyziklani_team_id.room');
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('e_fyziklani_team_id.name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task.label LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
            }
        });
        $this->setDataSource($dataSource);
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
            'points' => null,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        $this->flashMessage(_('Úloha byla smazána.'), 'success');
    }
}
