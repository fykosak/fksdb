<?php

namespace FKSDB\Authentication\SSO\ServiceSide;

use FKSDB\Authentication\SSO\IGlobalSession;
use FKSDB\Authentication\SSO\IGSIDHolder;
use LogicException;
use PDO;

/**
 * Read-only global session implementation (i.e. cannot allocate new GSID).
 *
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ReadonlyGlobalSession implements IGlobalSession {

    const TABLE = 'global_session';

    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var IGSIDHolder
     */
    private $gsidHolder;

    /**
     * @var array
     */
    private $data = array();

    function __construct(PDO $connection, IGSIDHolder $gsidHolder) {
        $this->connection = $connection;
        $this->gsidHolder = $gsidHolder;
    }

    public function getId() {
        return $this->gsidHolder->getGSID();
    }

    public function start() {
        $gsid = $this->gsidHolder->getGSID();
        if (!$gsid) {
            return;
        }

        $sql = 'SELECT login_id FROM `' . self::TABLE . '`
            where session_id = ?
            and since <= now()
            and until >= now()';

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array($gsid));

        $row = $stmt->fetch();

        if ($row) {
            $this->data[self::UID] = $row['login_id'];
        }
    }

    public function destroy() {
        // Note: This is read-only implementation, global session is not actually deleted.
        $this->gsidHolder->setGSID(null);
        $this->data = array();
    }

    /*     * *************************
     * ArrayAccess
     */

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new LogicException("This session is read-only.");
    }

    public function offsetUnset($offset) {
        throw new LogicException("This session is read-only.");
    }

}
