<?php


namespace FKSDB\Models\Fyziklani\Submit;

use Fykosak\Utils\Logging\ILogger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

use Nette\Security\User;
use Tracy\Debugger;

/**
 * Class SubmitHandler
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Handler {

    public const DEBUGGER_LOG_PRIORITY = 'fyziklani-info';

    public const LOG_FORMAT = 'Submit %d was %s by %s';

    private ServiceFyziklaniSubmit $serviceFyziklaniSubmit;

    private User $user;

    private TaskCodePreprocessor $taskCodePreprocessor;

    public function __construct(
        ModelEvent $event,
        ServiceFyziklaniTeam $serviceFyziklaniTeam,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        User $user
    ) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->user = $user;
        $this->taskCodePreprocessor = new TaskCodePreprocessor($event, $serviceFyziklaniTeam, $serviceFyziklaniTask);
    }

    /**
     * @param ILogger $logger
     * @param string $code
     * @param int $points
     * @return void
     * @throws PointsMismatchException
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    public function preProcess(ILogger $logger, string $code, int $points): void {
        $this->checkTaskCode($code);
        $this->savePoints($logger, $code, $points);
    }

    /**
     * @param ILogger $logger
     * @param string $code
     * @param int $points
     * @return void
     * @throws PointsMismatchException
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    private function savePoints(ILogger $logger, string $code, int $points): void {
        $task = $this->taskCodePreprocessor->getTask($code);
        $team = $this->taskCodePreprocessor->getTeam($code);

        $submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($task, $team);
        if (is_null($submit)) { // novo zadaný
            $this->createSubmit($logger, $task, $team, $points);
        } elseif (!$submit->isChecked()) { // check bodovania
            $this->checkSubmit($logger, $submit, $points);
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            $this->changePoints($logger, $submit, $points);
        } else {
            throw new TaskCodeException(\sprintf(_('Úloha je zadaná a overená.')));
        }
    }

    /**
     * @param string $code
     * @return void
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    private function checkTaskCode(string $code): void {
        $fullCode = $this->taskCodePreprocessor->createFullCode($code);
        /* skontroluje pratnosť kontrolu */
        if (!$this->taskCodePreprocessor->checkControlNumber($fullCode)) {
            throw new ControlMismatchException();
        }
        $team = $this->taskCodePreprocessor->getTeam($code);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($team);
        }
        $this->taskCodePreprocessor->getTask($code);
    }

    /**
     * @param ILogger $logger
     * @param ModelFyziklaniSubmit $submit
     * @param int $points
     * @return void
     * @throws ClosedSubmittingException
     */
    public function changePoints(ILogger $logger, ModelFyziklaniSubmit $submit, int $points): void {
        if (!$submit->canChange()) {
            throw new ClosedSubmittingException($submit->getFyziklaniTeam());
        }
        $this->serviceFyziklaniSubmit->updateModel2($submit, [
            'points' => $points,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'state' => ModelFyziklaniSubmit::STATE_CHECKED,
            'modified' => null,
        ]);
        $this->logEvent($submit, 'edited', \sprintf(' points %d', $points));
        $logger->log(new Message(\sprintf(_('Body byly upraveny. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $submit->getFyziklaniTeam()->name,
            $submit->getFyziklaniTeam()->e_fyziklani_team_id,
            $submit->getFyziklaniTask()->label,
            $submit->getFyziklaniTask()->name), Message::LVL_SUCCESS));
    }

    /**
     * @param ILogger $logger
     * @param ModelFyziklaniSubmit $submit
     * @return void
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     */
    public function revokeSubmit(ILogger $logger, ModelFyziklaniSubmit $submit): void {
        if ($submit->canRevoke(true)) {
            $this->serviceFyziklaniSubmit->updateModel2($submit, [
                'points' => null,
                'state' => ModelFyziklaniSubmit::STATE_NOT_CHECKED,
                /* ugly, exclude previous value of `modified` from query
                 * so that `modified` is set automatically by DB
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'modified' => null,
            ]);
            $this->logEvent($submit, 'revoked');
            $logger->log(new Message(\sprintf(_('Submit %d has been revoked.'), $submit->fyziklani_submit_id), Message::LVL_SUCCESS));
        }
    }

    /**
     * @param ILogger $logger
     * @param ModelFyziklaniSubmit $submit
     * @param int $points
     * @return void
     * @throws PointsMismatchException
     * @throws ClosedSubmittingException
     */
    public function checkSubmit(ILogger $logger, ModelFyziklaniSubmit $submit, int $points): void {
        if (!$submit->canChange()) {
            throw new ClosedSubmittingException($submit->getFyziklaniTeam());
        }
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->serviceFyziklaniSubmit->updateModel2($submit, [
            'state' => ModelFyziklaniSubmit::STATE_CHECKED,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null,
        ]);
        $this->logEvent($submit, 'checked');

        $logger->log(new Message(\sprintf(_('Bodovanie bolo overené. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $submit->getFyziklaniTeam()->name,
            $submit->getFyziklaniTeam()->e_fyziklani_team_id,
            $submit->getFyziklaniTask()->label,
            $submit->getFyziklaniTask()->name), Message::LVL_SUCCESS));
    }

    public function createSubmit(ILogger $logger, ModelFyziklaniTask $task, ModelFyziklaniTeam $team, int $points): void {
        $submit = $this->serviceFyziklaniSubmit->createNewModel([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'e_fyziklani_team_id' => $team->e_fyziklani_team_id,
            'state' => ModelFyziklaniSubmit::STATE_NOT_CHECKED,
            /* ugly, force current timestamp in database
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            //'created' => null, TODO!!!
        ]);
        $this->logEvent($submit, 'created', \sprintf(' points %d', $points));

        $logger->log(new Message(\sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $team->name,
            $team->e_fyziklani_team_id,
            $task->label,
            $task->name), Message::LVL_SUCCESS));
    }

    private function logEvent(ModelFyziklaniSubmit $submit, string $action, string $appendLog = null): void {
        Debugger::log(\sprintf(self::LOG_FORMAT . $appendLog, $submit->getPrimary(), $action, $this->user->getIdentity()->getId()), self::DEBUGGER_LOG_PRIORITY);
    }
}
