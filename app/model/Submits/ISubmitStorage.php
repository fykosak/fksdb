<?php

namespace Submits;

use FKSDB\ORM\ModelSubmit;


/**
 * Storage for signle file for each submit. Storage must keep original file
 * which can be modified by processings for later use.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ISubmitStorage {

    const TYPE_ORIGINAL = 0;
    const TYPE_PROCESSED = 1;

    /**
     * @return void
     */
    public function beginTransaction();

    /**
     * @throws SubmitStorageException for unsuccessful commit
     * @return void
     */
    public function commit();

    /**
     * @return void
     */
    public function rollback();

    /**
     * @param \Submits\IStorageProcessing $processing
     * @return void
     */
    public function addProcessing(IStorageProcessing $processing);

    /**
     * File is renamed/moved to own purposes.
     *
     * @param string $filename
     * @param ModelSubmit $submit
     * @return void
     */
    public function storeFile($filename, ModelSubmit $submit);

    /**
     *
     * @param \FKSDB\ORM\ModelSubmit $submit
     * @param int $type
     * @return string filename with absolute path
     */
    public function retrieveFile(ModelSubmit $submit, $type = self::TYPE_PROCESSED);

    /**
     * @param \FKSDB\ORM\ModelSubmit $submit
     * @return bool
     */
    public function existsFile(ModelSubmit $submit);

    /**
     * @param \FKSDB\ORM\ModelSubmit $submit
     */
    public function deleteFile(ModelSubmit $submit);
}


