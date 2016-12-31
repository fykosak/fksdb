<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 22.12.2016
 * Time: 2:41
 */

namespace FKSDB\model\Fyziklani;

use FKS\Utils\CSVParser;
use FyziklaniModule\BasePresenter;
use FyziklaniModule\TaskPresenter;
use ServiceFyziklaniTask;
use \Nette\Diagnostics\Debugger;


class FyziklaniTaskImportProcessor {
    
    /**
     *
     * @var ServiceFyziklaniTask 
     */
    private $serviceFyziklaniTask;

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

            try{
                if ($taskID) {
                    if ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                        $this->serviceFyziklaniTask->updateModel($task, [
                            'label' => $row['label'],
                            'name' => $row['name']
                        ]);
                        $this->serviceFyziklaniTask->save($task);
                        $messages[] = ['Úloha ' . $row['label'] . ' "' . $row['name'] . '" bola updatnuta', 'info'];
                    } else {
                        $messages[] = ['Úloha ' . $row['label'] . ' "' . $row['name'] . '" nebola pozmenená', 'warning'];
                    }
                } else {
                    $this->serviceFyziklaniTask->createNew($task, [
                        'label' => $row['label'],
                        'name' => $row['name'],
                        'event_id' => $this->eventID
                    ]);
                    $this->serviceFyziklaniTask->save($task);
                    $messages[] = ['Úloha ' . $row['label'] . ' "' . $row['name'] . '" bola vložená', 'success'];
                }
            }
            catch(Exception $e) {
                $messages[] = [_('Vyskytal sa chyba'), 'danger'];
                Debugger::log($e);
                $connection->rollBack();
                return;
            }
        }
        $connection->commit();
    }
}
