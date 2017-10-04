<?php

namespace FKSDB\model\Brawl;

use FKS\Utils\CSVParser;
use BrawlModule\TaskPresenter;
use ServiceBrawlTask;
use \Nette\Diagnostics\Debugger;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class TaskImportProcessor {

    /**
     *
     * @var ServiceBrawlTask
     */
    private $serviceBrawlTask;
    /**
     * @var integer
     */
    private $eventID;

    public function __construct($eventID, ServiceBrawlTask $serviceBrawlTask) {
        $this->eventID = $eventID;
        $this->serviceBrawlTask = $serviceBrawlTask;
    }

    public function __invoke($values, &$messages) {
        $filename = $values->csvfile->getTemporaryFile();
        $connection = $this->serviceBrawlTask->getConnection();
        $connection->beginTransaction();
        if ($values->state == TaskPresenter::IMPORT_STATE_REMOVE_N_INSERT) {
            $this->serviceBrawlTask->findAll($this->eventID)->delete();
        }
        $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
        foreach ($parser as $row) {
            try {
                $task = $this->serviceBrawlTask->findByLabel($row['label'], $this->eventID);
                if (!$task) {
                    $task = $this->serviceBrawlTask->createNew([
                        'label' => $row['label'],
                        'name' => $row['name'],
                        'event_id' => $this->eventID
                    ]);
                    $messages[] = [sprintf(_('Úloha %s "%s" bola vložena'), $row['label'], $row['name']), 'success'];
                } elseif ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                        $this->serviceBrawlTask->updateModel($task, [
                            'label' => $row['label'],
                            'name' => $row['name']
                        ]);
                        $messages[] = [sprintf(_('Úloha %s "%s" byla aktualizována'), $row['label'], $row['name']), 'info'];
                } else {
                        $messages[] = [
                            sprintf(_('Úloha %s "%s" nebyla aktualizována'), $row['label'], $row['name']),
                            'warning'
                        ];
                }
                $this->serviceBrawlTask->save($task);
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
