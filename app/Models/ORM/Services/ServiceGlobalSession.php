<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelGlobalSession;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Http\Request;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceGlobalSession extends AbstractServiceSingle {



    private const SESSION_ID_LENGTH = 32;
    private Request $request;

    public function __construct(string $tableName, string $modelClassName, Request $request, Explorer $context, Conventions $conventions) {
        parent::__construct($tableName, $modelClassName, $context, $conventions);
        $this->request = $request;
    }

    public function createSession(int $loginId, ?DateTime $until = null, ?DateTime $since = null): ModelGlobalSession {
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
}
