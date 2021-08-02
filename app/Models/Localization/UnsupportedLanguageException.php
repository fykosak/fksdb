<?php

declare(strict_types=1);

namespace FKSDB\Models\Localization;

use Nette\Application\BadRequestException;
use Nette\Http\IResponse;

class UnsupportedLanguageException extends BadRequestException
{

    public function __construct(string $lang, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf(_('Language %s is not supported'), $lang), IResponse::S400_BAD_REQUEST, $previous);
    }
}
