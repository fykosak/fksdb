<?php


namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Models\ModelTask;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\ISubmitStorage;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;

/**
 * Trait SubmitSaveTrait
 * @package FKSDB\Components\Control\AjaxUpload
 */
trait SubmitSaveTrait {
    /**
     * @param FileUpload $file
     * @param ModelTask $task
     * @param ModelContestant $contestant
     * @return AbstractModelSingle|ModelSubmit
     * @throws \Exception
     */
    private function saveSubmitTrait(FileUpload $file, ModelTask $task, ModelContestant $contestant) {
        $submit = $this->getServiceSubmit()->findByContestant($contestant->ct_id, $task->task_id);
        if (!$submit) {
            $submit = $this->getServiceSubmit()->createNewModel([
                'task_id' => $task->task_id,
                'ct_id' => $contestant->ct_id,
                'submitted_on' => new DateTime(),
                'source' => ModelSubmit::SOURCE_UPLOAD,
            ]);
        } else {
            $submit->update([
                'submitted_on' => new DateTime(),
                'source' => ModelSubmit::SOURCE_UPLOAD,
            ]);
        }
        // store file
        $this->getSubmitStorage()->storeFile($file->getTemporaryFile(), $submit);
        return $submit;
    }

    /**
     * @return ServiceSubmit
     */
    abstract protected function getServiceSubmit(): ServiceSubmit;

    /**
     * @return ISubmitStorage
     */
    abstract protected function getSubmitStorage():ISubmitStorage;
}
