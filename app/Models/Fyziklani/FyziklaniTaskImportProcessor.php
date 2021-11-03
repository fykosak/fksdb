<?php

namespace FKSDB\Models\Fyziklani;

use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Models\Utils\CSVParser;
use FKSDB\Modules\EventModule\Fyziklani\TaskPresenter;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class FyziklaniTaskImportProcessor {

    private ServiceFyziklaniTask $serviceFyziklaniTask;

    private ModelEvent $event;

    public function __construct(ServiceFyziklaniTask $serviceFyziklaniTask, ModelEvent $event) {
        $this->event = $event;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    public function process(ArrayHash $values, Logger $logger): void {
        $filename = $values->csvfile->getTemporaryFile();
        $connection = $this->serviceFyziklaniTask->explorer->getConnection();
        $connection->beginTransaction();
        if ($values->state == TaskPresenter::IMPORT_STATE_REMOVE_N_INSERT) {
            $this->event->getFyziklaniTasks()->delete();
        }
        $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
        foreach ($parser as $row) {
            try {
                $task = $this->serviceFyziklaniTask->findByLabel($row['label'], $this->event);
                if (!$task) {
                    $this->serviceFyziklaniTask->createNewModel([
                        'label' => $row['label'],
                        'name' => $row['name'],
                        'event_id' => $this->event->event_id,
                    ]);

                    $logger->log(new Message(sprintf(_('Task %s "%s" added'), $row['label'], $row['name']), BasePresenter::FLASH_SUCCESS));
                } elseif ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                    $this->serviceFyziklaniTask->updateModel($task, [
                        'label' => $row['label'],
                        'name' => $row['name'],
                    ]);
                    $logger->log(new Message(sprintf(_('Task %s "%s" updated'), $row['label'], $row['name']), BasePresenter::FLASH_INFO));
                } else {
                    $logger->log(new Message(
                        sprintf(_('Task %s "%s" not updated'), $row['label'], $row['name']), Message::LVL_WARNING));
                }
            } catch (\Exception $exception) {
                $logger->log(new Message(_('There was an error'), BasePresenter::FLASH_ERROR));
                Debugger::log($exception);
                $connection->rollBack();
                return;
            }
        }
        $connection->commit();
    }
}
