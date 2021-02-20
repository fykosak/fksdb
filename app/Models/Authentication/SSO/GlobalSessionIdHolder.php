<?php

namespace FKSDB\Models\Authentication\SSO;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface GlobalSessionIdHolder {

    public function getGlobalSessionId(): ?string;

    public function setGlobalSessionId(?string $globalSessionId): void;
}