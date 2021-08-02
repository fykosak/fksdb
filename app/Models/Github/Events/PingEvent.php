<?php

declare(strict_types=1);

namespace FKSDB\Models\Github\Events;

class PingEvent extends Event
{

    public string $zen;

    public string $hook_id;
}
