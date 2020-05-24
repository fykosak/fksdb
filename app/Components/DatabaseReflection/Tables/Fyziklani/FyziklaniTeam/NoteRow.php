<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

use FKSDB\Components\DatabaseReflection\DefaultPrinterTrait;

/**
 * Class NoteRow
 * *
 */
class NoteRow extends AbstractFyziklaniTeamRow {
    use DefaultPrinterTrait;

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Note');
    }

    /**
     * @return string
     */
    protected function getModelAccessKey(): string {
        return 'note';
    }
}
