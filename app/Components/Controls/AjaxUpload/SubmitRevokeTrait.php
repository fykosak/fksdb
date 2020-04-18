<?php

namespace FKSDB\Components\Control\AjaxUpload;

use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelSubmit;
use FKSDB\ORM\Services\ServiceSubmit;
use FKSDB\Submits\FilesystemUploadedSubmitStorage;
use FKSDB\Submits\StorageException;
use FKSDB\Exceptions\ModelException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use PublicModule\SubmitPresenter;
use Tracy\Debugger;

/**
 * Trait SubmitRevokeTrait
 * @package FKSDB\Components\Control\AjaxUpload
 */
trait SubmitRevokeTrait {

    /**
     * @param int $submitId
     * @return array
     * @throws InvalidLinkException
     */
    public function traitHandleRevoke(int $submitId): array {
        /** @var ServiceSubmit $serviceSubmit */
        $serviceSubmit = $this->getContext()->getByType(ServiceSubmit::class);
        /** @var FilesystemUploadedSubmitStorage $submitUploadedStorage */
        $submitUploadedStorage = $this->getContext()->getByType(FilesystemUploadedSubmitStorage::class);
        /** @var ModelSubmit $submit */
        $submit = $serviceSubmit->findByPrimary($submitId);
        if (!$submit) {
            return [new Message(_('Neexistující submit.'), ILogger::ERROR), null];
        }
        $contest = $submit->getContestant()->getContest();
        if (!$this->getPresenter()->getContestAuthorizator()->isAllowed($submit, 'revoke', $contest)) {
            return [new Message(_('Nedostatečné oprávnění.'), ILogger::ERROR), null];
        }
        if (!$this->canRevoke($submit)) {
            return [new Message(_('Nelze zrušit submit.'), ILogger::ERROR), null];
        }
        try {
            $submitUploadedStorage->deleteFile($submit);
            $serviceSubmit->dispose($submit);
            $data = $serviceSubmit->serializeSubmit(null, $submit->getTask(), $this->getPresenter());

            return [new Message(\sprintf('Odevzdání úlohy %s zrušeno.', $submit->getTask()->getFQName()), ILogger::WARNING), $data];

        } catch (StorageException $exception) {
            Debugger::log($exception);
            return [new Message(_('Během mazání úlohy %s došlo k chybě.'), ILogger::ERROR), null];
        } catch (ModelException $exception) {
            Debugger::log($exception);
            return [new Message(_('Během mazání úlohy %s došlo k chybě.'), ILogger::ERROR), null];
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

    /**
     * @return Container
     */
    abstract function getContext();
}
