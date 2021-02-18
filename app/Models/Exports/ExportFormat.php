<?php

namespace FKSDB\Models\Exports;

use Nette\Application\IResponse;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ExportFormat {
    public function getResponse(): IResponse;
}
