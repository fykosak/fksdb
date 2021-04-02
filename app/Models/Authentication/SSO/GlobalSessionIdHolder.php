<?php

namespace FKSDB\Models\Authentication\SSO;

interface GlobalSessionIdHolder {

    public function getGlobalSessionId(): ?string;

    public function setGlobalSessionId(?string $globalSessionId): void;
}
