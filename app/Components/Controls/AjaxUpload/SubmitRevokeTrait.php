<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemSubmitUploadedStorage;
use FKSDB\Submits\StorageException;
use ModelException;
use Nette\Application\UI\InvalidLinkException;
use PublicModule\SubmitPresenter;
use Tracy\Debugger;
use function sprintf;

/**
 * Trait SubmitRevokeTrait
 * @package FKSDB\Components\Control\AjaxUpload
 */
trait SubmitRevokeTrait {
    /**
     * @return ServiceSubmit
     */
    abstract protected function getServiceSubmit(): ServiceSubmit;

    /**
     * @param bool $need
     * @return SubmitPresenter
     */
    abstract protected function getPresenter($need = true);

    /**
     * @return FilesystemSubmitUploadedStorage
     */
    abstract protected function getSubmitUploadedStorage(): FilesystemSubmitUploadedStorage;

    /**
     * @param int $submitId
     * @return array
     * @throws InvalidLinkException
     */
    public function traitHandleRevoke(int $submitId): array {

        /**
         * @var ModelSubmit $submit
         */
        $submit = $this->getServiceSubmit()->findByPrimary($submitId);
        if (!$submit) {
            return [new Message(_('Neexistující submit.'), 'danger'), null];
        }
        $contest = $submit->getContestant()->getContest();
        if (!$this->getPresenter()->getContestAuthorizator()->isAllowed($submit, 'revoke', $contest)) {
            return [new Message(_('Nedostatečné oprávnění.'), 'danger'), null];
        }
        if (!$this->canRevoke($submit)) {
            return [new Message(_('Nelze zrušit submit.'), 'danger'), null];
        }
        try {
            $this->getSubmitUploadedStorage()->deleteFile($submit);
            $this->getServiceSubmit()->dispose($submit);
            $data = $this->getServiceSubmit()->serializeSubmit(null, $submit->getTask(), $this->getPresenter());

            return [new Message(sprintf('Odevzdání úlohy %s zrušeno.', $submit->getTask()->getFQName()), 'warning'), $data];

        } catch (StorageException $exception) {
            Debugger::log($exception);
            return [new Message(_('Během mazání úlohy %s došlo k chybě.'), 'danger'), null];
        } catch (ModelException $exception) {
            Debugger::log($exception);
            return [new Message(_('Během mazání úlohy %s došlo k chybě.'), 'danger'), null];
        }
    }

    /**
     * @param ModelSubmit $submit
     * @return bool
     * @internal
     */
    public function canRevoke(ModelSubmit $submit): bool {
        if ($submit->source != ModelSubmit::SOURCE_UPLOAD) {
            return false;
        }

        $now = time();
        $start = $submit->getTask()->submit_start ? $submit->getTask()->submit_start->getTimestamp() : 0;
        $deadline = $submit->getTask()->submit_deadline ? $submit->getTask()->submit_deadline->getTimestamp() : ($now + 1);

        return ($now <= $deadline) && ($now >= $start);
    }
}
