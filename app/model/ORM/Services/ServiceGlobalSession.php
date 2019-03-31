<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelGlobalSession;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\DateTime;
use Nette\Http\Request;
use Nette\Utils\Random;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGlobalSession extends AbstractServiceSingle {

    const SESSION_ID_LENGTH = 32;

    /**
     * @return string
     */
    protected function getModelClassName(): string {
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
     * @param Context $connection
     * @param IConventions $conventions
     */
    function __construct(Request $request, Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions);
        $this->request = $request;
    }

    /**
     *
     * @param string $loginId
     * @param DateTime $until
     * @param DateTime $since
     * @return ModelAuthToken
     */
    public function createSession($loginId, DateTime $until = null, DateTime $since = null) {
        if ($since === null) {
            $since = new DateTime();
        }

        $this->getConnection()->beginTransaction();

        do {
            $sessionId = Random::generate(self::SESSION_ID_LENGTH, 'a-zA-Z0-9');
        } while ($this->findByPrimary($sessionId));

        $session = $this->createNew([
            'session_id' => $sessionId,
            'login_id' => $loginId,
            'since' => $since,
            'until' => $until,
            'remote_ip' => $this->request->getRemoteAddress(),
        ]);
        $this->save($session);
        $this->getConnection()->commit();

        return $session;
    }

    //TODO garbage collection
}

