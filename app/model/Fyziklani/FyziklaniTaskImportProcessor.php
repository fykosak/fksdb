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


class FyziklaniTaskImportProcessor {
    /**
     * @var BasePresenter
     * @deprecated
     */
    private $presenter;

    private $eventID;

    public function __construct(BasePresenter $presenter, $eventID) {
        $this->eventID = $eventID;
        $this->presenter = $presenter;
    }

    public function __invoke($values, &$messages) {
        $filename = $values->csvfile->getTemporaryFile();
        if ($values->state == TaskPresenter::IMPORT_STATE_REMOVE_N_INSERT) {
            $this->presenter->database->query('DELETE FROM ' . \DbNames::TAB_FYZIKLANI_TASK . ' WHERE event_id=?', $this->eventID);
        }
        $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
        foreach ($parser as $row) {

            $task = $this->presenter->database->table(\DbNames::TAB_FYZIKLANI_TASK)->where('event_id', $this->eventID)->where('label', $row['label'])->fetch();
            $taskID = $task ? $task->fyziklani_task_id : false;

            if ($taskID) {
                if ($values->state == TaskPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                    if ($this->presenter->database->query('UPDATE ' . \DbNames::TAB_FYZIKLANI_TASK . ' SET ? WHERE fyziklani_task_id =?', [
                        'label' => $row['label'],
                        'name' => $row['name']
                    ], $taskID)
                    ) {
                        $messages[] = ['Úloha ' . $row['label'] . ' "' . $row['name'] . '" bola updatnuta', 'info'];
                    } else {
                        $messages[] = [_('Vyskytal sa chyba'), 'danger'];
                    }
                } else {
                    $messages[] = ['Úloha ' . $row['label'] . ' "' . $row['name'] . '" nebola pozmenená', 'warning'];
                }
            } else {
                if ($this->presenter->database->query('INSERT INTO ' . \DbNames::TAB_FYZIKLANI_TASK, [
                    'label' => $row['label'],
                    'name' => $row['name'],
                    'event_id' => $this->eventID
                ])
                ) {
                    $messages[] = ['Úloha ' . $row['label'] . ' "' . $row['name'] . '" bola vložená', 'success'];
                } else {
                    $messages[] = [_('Vyskytal sa chyba'), 'danger'];
                }
            }
        }
    }
}
