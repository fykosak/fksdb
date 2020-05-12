<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Utils\CSVParser;
use FyziklaniModule\TaskPresenter;
use Nette\DI\Container;
use Tracy\Debugger;

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
     * @param $values
     * @param $messages
     */
    public function __invoke($values, &$messages) {
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
                    $messages[] = [sprintf(_('Úloha %s "%s" bola vložena'), $row['label'], $row['name']), \BasePresenter::FLASH_SUCCESS];
                } elseif ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                    $this->serviceFyziklaniTask->updateModel2($task, [
                        'label' => $row['label'],
                        'name' => $row['name']
                    ]);
                    $messages[] = [sprintf(_('Úloha %s "%s" byla aktualizována'), $row['label'], $row['name']), \BasePresenter::FLASH_INFO];
                } else {
                    $messages[] = [
                        sprintf(_('Úloha %s "%s" nebyla aktualizována'), $row['label'], $row['name']),
                        ILogger::WARNING
                    ];
                }
            } catch (\Exception $exception) {
                $messages[] = [_('Vyskytla se chyba'), \BasePresenter::FLASH_ERROR];
                Debugger::log($exception);
                $connection->rollBack();
                return;
            }
        }
        $connection->commit();
    }
}
