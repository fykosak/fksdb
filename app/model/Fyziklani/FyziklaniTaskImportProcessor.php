<?php

namespace FKSDB\Fyziklani;

use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Utils\CSVParser;
use FKSDB\Modules\FyziklaniModule\TaskPresenter;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class FyziklaniTaskImportProcessor {

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniTaskImportProcessor constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        $this->event = $event;
        $this->serviceFyziklaniTask = $container->getByType(ServiceFyziklaniTask::class);
    }

    /**
     * @param ArrayHash|array $values
     * @param ILogger $logger
     */
    public function __invoke($values, ILogger $logger) {
        $filename = $values->csvfile->getTemporaryFile();
        $connection = $this->serviceFyziklaniTask->getConnection();
        $connection->beginTransaction();
        if ($values->state == TaskPresenter::IMPORT_STATE_REMOVE_N_INSERT) {
            $this->serviceFyziklaniTask->findAll($this->event)->delete();
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

                    $logger->log(new Message(sprintf(_('Úloha %s "%s" bola vložena'), $row['label'], $row['name']), BasePresenter::FLASH_SUCCESS));
                } elseif ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                    $this->serviceFyziklaniTask->updateModel2($task, [
                        'label' => $row['label'],
                        'name' => $row['name']
                    ]);
                    $logger->log(new Message(sprintf(_('Úloha %s "%s" byla aktualizována'), $row['label'], $row['name']), BasePresenter::FLASH_INFO));
                } else {
                    $logger->log(new Message(
                        sprintf(_('Úloha %s "%s" nebyla aktualizována'), $row['label'], $row['name']), ILogger::WARNING));
                }
            } catch (\Exception $exception) {
                $logger->log(new Message(_('Vyskytla se chyba'), BasePresenter::FLASH_ERROR));
                Debugger::log($exception);
                $connection->rollBack();
                return;
            }
        }
        $connection->commit();
    }
}
