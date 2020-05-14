<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelGlobalSession;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Http\Request;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGlobalSession extends AbstractServiceSingle {

    const SESSION_ID_LENGTH = 32;

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelGlobalSession::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_GLOBAL_SESSION;
    }

    /**
     * @var Request
     */
    private $request;

    /**
     * FKSDB\ORM\Services\ServiceGlobalSession constructor.
     * @param Request $request
     * @param Context $context
     * @param IConventions $conventions
     */
    public function __construct(Request $request, Context $context, IConventions $conventions) {
        parent::__construct($context, $conventions);
        $this->request = $request;
    }

    /**
     *
     * @param string $loginId
     * @param DateTime $until
     * @param DateTime $since
     * @return ModelGlobalSession
     * @throws \Exception
     */
    public function createSession($loginId, DateTime $until = null, DateTime $since = null): ModelGlobalSession {
        if ($since === null) {
            $since = new DateTime();
        }

        $this->context->getConnection()->beginTransaction();

        do {
            $sessionId = Random::generate(self::SESSION_ID_LENGTH, 'a-zA-Z0-9');
        } while ($this->findByPrimary($sessionId));
        /** @var ModelGlobalSession $session */
        $session = $this->createNewModel([
            'session_id' => $sessionId,
            'login_id' => $loginId,
            'since' => $since,
            'until' => $until,
            'remote_ip' => $this->request->getRemoteAddress(),
        ]);
        // $this->save($session);
        $this->getConnection()->commit();

        return $session;
    }

    //TODO garbage collection
}
