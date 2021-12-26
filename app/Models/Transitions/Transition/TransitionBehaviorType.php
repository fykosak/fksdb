<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

enum TransitionBehaviorType: string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGEROUS = 'danger';
    case PRIMARY = 'primary';
    case DEFAULT = 'secondary';
}
