<?php
/**
 *
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @todo original author?
 * @todo doc
 * @todo cc wrt variable/method names
 */
/**
 * Class Upload
 */
class Upload extends Tdb
{
    private $initialized = false;
    private $file = array();
    private $maxSize;
    private $uploadLoc;

    /**
     * Class instantiation
     *
     * @param String $dir
     * @return bool, true on success
     */
    function __construct($dir, $maxSize, $uploadLoc)
    {
        // Make sure we start fresh
        $this->initialized = false;
        $this->uploadLoc = $uploadLoc;
        // Initialize the TextDb object
        $this->tdb($dir . '/', 'main.tdb');

        // Check if the upload mod has been installed
        if (!file_exists($dir . '/main_uploads.ta'))
            $this->sendError(E_USER_ERROR, 'Upload database has not been installed', __LINE__);
        else {
            // Set this file pointer
            $this->setFp('uploads', 'uploads');
            $this->maxSize = $maxSize * 1024; // Maxsize is given in KB we want bytes
            $this->initialized = true;

            return true;
        }

        return false;
    }

    /**
     * Stores an uploaded file in the database
     *
     * @param String[] $file - just send $_FILES['file_field']
     * @return bool
     */
    public function storeFile($file, $forum_id = '', $topic_id = '')
    {
        if (!$this->initialized) {
            $this->notInitialized();

            return false;
        }

        if ($file['error'] != UPLOAD_ERR_OK)
            return false;

        if ($this->maxSize < $file['size'])
            return false;

        if (is_uploaded_file($file['tmp_name'])) {
            clearstatcache();
            $file_name = md5(uniqid(rand(), true));
            move_uploaded_file($file['tmp_name'], $this->uploadLoc . '/' . $file_name);

            $id = $this->add('uploads', array(
                    'name' => $file['name'],
                    // 'type' => $file['type'],
                    'size' => $file['size'],
                    'downloads' => 0,
                    'file_loca' => $file_name,
                    'forum_id' 	=> $forum_id,
                    'topic_id'	=> $topic_id
            ));

            return (int)$id;
        }

        return false;
    }

    public function deleteFile($id)
    {
        $this->getFile($id);
        $this->delete('uploads', $id);

        return unlink($this->uploadLoc . '/' . $this->file['file_loca']);
    }

    public function getFile($id)
    {
        if (!$this->initialized) {
            $this->notInitialized();

            return false;
        }

        // Retrieve the file from the database
        $q = $this->get('uploads', $id);

        if ($q !== false && !empty($q[0])) {
            $this->file = $q[0];

            return true;
        } else {
            $this->sendError(E_USER_ERROR, 'Unable to fetch the file from the upload\'s table (UploadId: <b>' . $id . '</b>)', __LINE__);

            return false;
        }
    }

    public function dumpFile()
    {
        if (!$this->initialized) {
            $this->notInitialized();

            return false;
        }

        // Pre-dump checks
        if (empty($this->file)) {
            $this->sendError(E_USER_NOTICE, 'No file loaded, cannot dump', __LINE__);

            return false;
        }

        if (headers_sent()) {
            $this->sendError(E_USER_NOTICE, 'Headers have already been sent, unable to dump file', __LINE__);

            return false;
        }

        if (!file_exists($this->uploadLoc . '/' . $this->file['file_loca'])) {
            $this->sendError('The file does not exist.', __LINE__);

            return false;
        }

        // Dump the file to the browser
        header('Content-type: application/octet-stream');
        header('Content-Length: ' . filesize($this->uploadLoc . '/' . $this->file['file_loca']));
        header('Content-disposition: attachment; filename="' . $this->file['name'] . '"');
        readfile($this->uploadLoc . '/' . $this->file['file_loca']);
    }

    public function notInitialized()
    {
        $this->sendError(E_USER_NOTICE, 'The upload class has not been initialized');

        return true;
    }
}