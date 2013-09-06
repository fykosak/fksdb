<?php

use Nette\InvalidStateException;

require_once('pdf_parser.php');

class fks_pdf_parser_exception extends InvalidStateException {
    
}
class fks_pdf_parser extends pdf_parser {
    public function error($msg) {
        throw new fks_pdf_parser_exception($msg);
    }
}

