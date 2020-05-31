<?php

namespace Exports\Formats;

use Nette\Application\IResponse;
use Nette\Http\IRequest;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class PlainTextResponse implements IResponse {
    use SmartObject;

    private string $content;
    /** @var */
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

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function send(IRequest $httpRequest, \Nette\Http\IResponse $httpResponse): void {
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
