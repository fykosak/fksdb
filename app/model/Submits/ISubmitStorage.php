<?php

namespace FKSDB\Submits;

use FKSDB\ORM\Models\ModelSubmit;


/**
 * Storage for signle file for each submit. Storage must keep original file
 * which can be modified by processings for later use.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface ISubmitStorage {

    public const TYPE_ORIGINAL = 0;
    public const TYPE_PROCESSED = 1;

    public function beginTransaction(): void;

    /**
     * @return void
     * @throws StorageException for unsuccessful commit
     */
    public function commit(): void;

    public function rollback(): void;

    public function addProcessing(IStorageProcessing $processing): void;

    /**
     * File is renamed/moved to own purposes.
     *
     * @param string $filename
     * @param ModelSubmit $submit
     * @return void
     */
    public function storeFile(string $filename, ModelSubmit $submit): void;

    /**
     *
     * @param ModelSubmit $submit
     * @param int $type
     * @return string filename with absolute path
     */
    public function retrieveFile(ModelSubmit $submit, $type = self::TYPE_PROCESSED): ?string;

    public function fileExists(ModelSubmit $submit): bool;

    public function deleteFile(ModelSubmit $submit): void;
}
