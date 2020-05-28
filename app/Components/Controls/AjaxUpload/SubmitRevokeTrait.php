<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FileSystemStorage\UploadedStorage;
use FKSDB\Submits\StorageException;
use FKSDB\Exceptions\ModelException;
use Nette\Application\UI\InvalidLinkException;
use PublicModule\SubmitPresenter;
use Tracy\Debugger;

/**
 * Trait SubmitRevokeTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait SubmitRevokeTrait {

    /**
     * @param ILogger $logger
     * @param int $submitId
     * @return array|null
     * @throws InvalidLinkException
     */
    public function traitHandleRevoke(ILogger $logger, int $submitId) {
        /** @var ModelSubmit $submit */
        $submit = $this->getServiceSubmit()->findByPrimary($submitId);
        if (!$submit) {
            return [new Message(_('Neexistující submit.'), ILogger::ERROR), null];
        }
        $contest = $submit->getContestant()->getContest();
        if (!$this->getPresenter()->getContestAuthorizator()->isAllowed($submit, 'revoke', $contest)) {
            $logger->log(new Message(_('Nedostatečné oprávnění.'), ILogger::ERROR));
            return null;
        }
        if (!$this->canRevoke($submit)) {
            $logger->log(new Message(_('Nelze zrušit submit.'), ILogger::ERROR));
            return null;
        }
        try {
            $this->getUploadedStorage()->deleteFile($submit);
            $this->getServiceSubmit()->dispose($submit);
            $data = $this->getServiceSubmit()->serializeSubmit(null, $submit->getTask(), $this->getPresenter());
            $logger->log(new Message(\sprintf('Odevzdání úlohy %s zrušeno.', $submit->getTask()->getFQName()), ILogger::WARNING));
            return $data;

        } catch (StorageException $exception) {
            Debugger::log($exception);
            $logger->log(new Message(_('Během mazání úlohy %s došlo k chybě.'), ILogger::ERROR));
            return null;
        } catch (ModelException $exception) {
            Debugger::log($exception);
            $logger->log(new Message(_('Během mazání úlohy %s došlo k chybě.'), ILogger::ERROR));
            return null;
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

    /**
     * @param bool $need
     * @return SubmitPresenter
     */
    abstract protected function getPresenter($need = true);

    abstract protected function getUploadedStorage(): UploadedStorage;

    abstract protected function getServiceSubmit(): ServiceSubmit;
}
