<?php
/**
 * Created by PhpStorm.
 * User: miso
 * Date: 22.12.2016
 * Time: 2:41
 */

namespace FKSDB\model\Fyziklani;

use FKS\Utils\CSVParser;
use \OrgModule\FyziklaniPresenter;

class FyziklaniTaskImportProcessor {
    /**
     * @var FyziklaniPresenter
     */
    private $presenter;

    public function __construct($presenter) {
        $this->presenter = $presenter;
    }

    public function preprosess($values) {
        $filename = $values->csvfile->getTemporaryFile();
        if ($values->state == FyziklaniPresenter::IMPORT_STATE_REMOVE_N_INSERT) {
            $this->presenter->database->query('DELETE FROM ' . \DbNames::TAB_FYZIKLANI_TASK . ' WHERE event_id=?', $this->presenter->eventID);
        }
        $parser = new CSVParser($filename, CSVParser::INDEX_FROM_HEADER);
        foreach ($parser as $row) {
            $taskID = $this->presenter->taskLabelToTaskID($row['label']);
            if ($taskID) {
                if ($values->state == FyziklaniPresenter::IMPORT_STATE_UPDATE_N_INSERT) {
                    if ($this->presenter->database->query('UPDATE ' . \DbNames::TAB_FYZIKLANI_TASK . ' SET ? WHERE fyziklani_task_id =?', ['label' => $row['label'], 'name' => $row['name']], $taskID)) {
                        $this->presenter->flashMessage('Úloha ' . $row['label'] . ' "' . $row['name'] . '" bola updatnuta', 'info');
                    } else {
                        $this->presenter->flashMessage(_('Vyskytal sa chyba'), 'danger');
                    }
                } else {
                    $this->presenter->flashMessage('Úloha ' . $row['label'] . ' "' . $row['name'] . '" nebola pozmenená', 'warning');
                }
            } else {
                if ($this->presenter->database->query('INSERT INTO ' . \DbNames::TAB_FYZIKLANI_TASK, ['label' => $row['label'], 'name' => $row['name'], 'event_id' => $this->presenter->eventID])) {
                    $this->presenter->flashMessage('Úloa ' . $row['label'] . ' "' . $row['name'] . '" bola vložená', 'success');
                } else {
                    $this->presenter->flashMessage(_('Vyskytal sa chyba'), 'danger');
                }
            }
        }
    }


}