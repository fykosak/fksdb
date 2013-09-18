<?php

namespace Submits;

use ModelSubmit;
use Nette\Http\FileUpload;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ISubmitStorage {

    public function beginTransaction();

    public function commit();

    public function rollback();

    public function storeFile(FileUpload $file, ModelSubmit $submit);

    public function retrieveFile(ModelSubmit $submit);

    public function existsFile(ModelSubmit $submit);
    
    public function deleteFile(ModelSubmit $submit);
}

?>
