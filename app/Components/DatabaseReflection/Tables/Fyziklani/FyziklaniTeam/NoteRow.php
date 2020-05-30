<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NoteRow
 * *
 */
class NoteRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    public function getTitle(): string {
        return _('Note');
    }

    protected function getModelAccessKey(): string {
        return 'note';
    }
}
