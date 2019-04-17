<?php

namespace FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam;

/**
 * Class NoteRow
 * @package FKSDB\Components\DatabaseReflection\Fyziklani\FyziklaniTeam
 */
class NoteRow extends AbstractFyziklaniTeamRow {
    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Note');
    }
}
