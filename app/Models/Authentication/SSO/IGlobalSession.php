<?php

namespace FKSDB\Models\Authentication\SSO;

use ArrayAccess;

/**
 * For normal sessions we store client identification (session ID) in cookies
 * and data on server. The global session allows sharing same data across
 * multiple domains (i.e. actually different sessions).
 *
 * It's similar to normal sessions -- instead of cookies we have normal sessions
 * and session storage itself depends on the implementation.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IGlobalSession extends ArrayAccess {

    /** @const Session key that holds authenticated user UI. */

    public const UID = 'uid';

    public function start(): void;

    public function getId(): ?string;

    public function destroy(): void;
}
