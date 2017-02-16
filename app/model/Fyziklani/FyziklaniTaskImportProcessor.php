<?php

namespace FKSDB\model\Fyziklani;

use FKS\Utils\CSVParser;
use FyziklaniModule\TaskPresenter;
use ServiceFyziklaniTask;
use \Nette\Diagnostics\Debugger;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class FyziklaniTaskImportProcessor {

    /**
     *
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var integer
     */
    private $eventID;

    public function __construct($eventID, ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->eventID = $eventID;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    public function __invoke($values, &$messages) {
        $filename = $values->csvfile->getTemporaryFile();
        $connection = $this->serviceFyziklaniTask->getConnection();
        $connection->beginTransaction();
        if ($values->state == TaskPresenter::IMPORT_STATE_REMOVE_N_INSERT) {
            $this->serviceFyziklaniTask->findAll($this->eventID)->delete();
        }
        $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
        foreach ($parser as $row) {
            $task = $this->serviceFyziklaniTask->findByLabel($row['label'], $this->eventID);
            $taskID = $task ? $task->fyziklani_task_id : false;
            try {
                if ($taskID) {
                    if ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                        $this->serviceFyziklaniTask->updateModel($task, [
                            'label' => $row['label'],
                            'name' => $row['name']
                        ]);
                        $this->serviceFyziklaniTask->save($task);
                        $messages[] = [sprintf(_('Úloha %s "%s" byla aktualizována'), $row['label'], $row['name']), 'info'];
                    } else {
                        $messages[] = [
                            sprintf(_('Úloha %s "%s" nebyla aktualizována'), $row['label'], $row['name']),
                            'warning'
                        ];
                    }
                } else {
                    $task = $this->serviceFyziklaniTask->createNew([
                        'label' => $row['label'],
                        'name' => $row['name'],
                        'event_id' => $this->eventID
                    ]);
                    $this->serviceFyziklaniTask->save($task);
                    $messages[] = [sprintf(_('Úloha %s "%s" bola vložena'), $row['label'], $row['name']), 'success'];
                }
            } catch (Exception $e) {
                $messages[] = [_('Vyskytla se chyba'), 'danger'];
                Debugger::log($e);
                $connection->rollBack();
                return;
            }
        }
        $connection->commit();
    }
}
