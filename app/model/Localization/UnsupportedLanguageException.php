<?php

namespace FKSDB\Localization;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

/**
 * Class UnsupportedLanguageException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UnsupportedLanguageException extends BadRequestException {
    /**
     * UnsupportedLanguageException constructor.
     * @param string $lang
     * @param \Exception|null $previous
     */
    public function __construct(string $lang, \Exception $previous = null) {
        parent::__construct(sprintf(_('Language %s is not supported'), $lang), IResponse::S400_BAD_REQUEST, $previous);
    }

}
