<?php

namespace FKSDB\Authentication\SSO;

use ArrayAccess;

/**
 * For normal sessions we store client identification (session ID) in cookies
 * and data on server. The global session allows sharing same data across
 * multiple domains (i.e. actually different sessions).
 *
 * It's similar to normal sessions -- instead of cookies we have nomal sessions
 * and session storage itself depends on the implementation.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IGlobalSession extends ArrayAccess {
    /** @const Session key that holds authenticated user UI. */

    public const UID = 'uid';

    /**
     * @return void
     */
    public function start();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return void
     */
    public function destroy();
}
