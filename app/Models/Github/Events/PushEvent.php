<?php

namespace FKSDB\Models\Github\Events;

class PushEvent extends Event {

    public const REFS_HEADS = 'refs/heads/';

    public string $ref;

    public string $after;

    public string $before;
}
