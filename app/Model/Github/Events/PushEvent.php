<?php

namespace FKSDB\Model\Github\Events;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PushEvent extends Event {

    public const REFS_HEADS = 'refs/heads/';

    public string $ref;

    public string $after;

    public string $before;
}
