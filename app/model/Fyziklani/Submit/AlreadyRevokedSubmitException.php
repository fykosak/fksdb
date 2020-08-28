<?php

namespace FKSDB\Fyziklani\Submit;

use Nette\Application\BadRequestException;
use Nette\Http\Response;

/**
 * Class AlreadyRevokedException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AlreadyRevokedSubmitException extends BadRequestException {
    /**
     * AlreadyRevokedException constructor.
     * @param \Exception|null $previous
     */
    public function __construct(\Exception $previous = null) {
        parent::__construct(_('Submit is already revoked'), Response::S400_BAD_REQUEST, $previous);
    }
}
