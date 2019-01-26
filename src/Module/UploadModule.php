<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 25/01/2019
 * Time: 21:48
 */

namespace Plexus\Module;


use Plexus\Application;
use Plexus\Module;
use Symfony\Component\Yaml\Exception\RuntimeException;

class UploadModule extends Module
{

    public $NO_FILE_INPUT = "No file input was detected.";

    public $INVALID_PARAMETER = "Invalid parameter.";

    public $INVALID_FILE_FORMAT = "Invalid file format.";

    public $NO_FILE = "No file sent.";

    public $FILESIZE_LIMIT = "Exceeded filesize limit.";

    public $UNNKOWN_ERRORS = "Unknown errors.";

    public $MOVE_FAILED = "Failed to move uploaded file.";


    /**
     * UploadModule constructor.
     * @param Application $application
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     */
    public function __construct(Application $application) {
        parent::__construct('UploadModule', $application);
    }

    public function handleFileUpload($name, $upload_dir, $maxsize=-1, $mimes=[]) {

        // Check that there is a file input
        if (!isset($_FILES[$name])) {
            throw new \RuntimeException($this->NO_FILE_INPUT);
        }

        // Check that the request is : Defined, Single File, Not corrupted
        if (!isset($_FILES[$name]['error']) || is_array($_FILES[$name]['error'])) {
            throw new \RuntimeException($this->INVALID_PARAMETER);
        }

        // Check the errors
        switch ($_FILES[$name]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException($this->FILESIZE_LIMIT);
            default:
                throw new \RuntimeException($this->UNNKOWN_ERRORS);
        }

        // Check the file's size
        if ($maxsize > 0 && $_FILES[$name]['size']) {
            throw new \RuntimeException($this->FILESIZE_LIMIT);
        }

        // Check MIME Type
        // Example :$mimes = array('jpg' => 'image/jpeg', 'png' => 'image/png');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (false === $extension = array_search(
                $finfo->file($_FILES[$name]['tmp_name']),
                $mimes,
                true
            )) {
            if (count($mimes) > 0) {
                throw new \RuntimeException($this->INVALID_FILE_FORMAT);
            }
        }

        $acc = 0;
        while (
            is_file(
                sprintf(
                    '%s/%s%s.%s',
                    $upload_dir, sha1($_FILES[$name]['tmp_name']),
                    strval($acc),
                (($extension === false) ? 'ext' : $extension )
                )
            )
        ) {
            $acc += 1;
        }

        if (!move_uploaded_file(
            $_FILES[$name]['tmp_name'],
            sprintf(
                '%s/%s%s.%s',
                $upload_dir, sha1($_FILES[$name]['tmp_name']),
                strval($acc),
                (($extension === false) ? 'ext' : $extension )
            )
        )) {
            throw new \RuntimeException($this->MOVE_FAILED);
        }

        return sprintf(
            '%s/%s%s.%s',
            $upload_dir, sha1($_FILES[$name]['tmp_name']),
            strval($acc),
            (($extension === false) ? 'ext' : $extension )
        );


    }
}