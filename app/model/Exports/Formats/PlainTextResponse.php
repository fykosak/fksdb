<?php

namespace Exports\Formats;

use Nette\Application\IResponse;
use Nette\Http\IResponse as HttpResponse;
use Nette\Http\IRequest;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PlainTextResponse implements IResponse {
    use SmartObject;

    /** @var string */
    private $content;
    /** @var string */
    private $name;

    /**
     * PlainTextResponse constructor.
     * @param $content
     */
    public function __construct(string $content) {
        $this->content = $content;

    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * @param IRequest $httpRequest
     * @param HttpResponse $httpResponse
     * @return void
     */
    public function send(IRequest $httpRequest, HttpResponse $httpResponse) {
        $httpResponse->setContentType('text/plain', 'utf-8');

        if ($this->name) {
            $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . $this->name . '"');
        } else {
            $httpResponse->setHeader('Content-Disposition', 'attachment');
        }

        $httpResponse->setHeader('Content-Length', strlen($this->content));
        echo $this->content;
    }

}
