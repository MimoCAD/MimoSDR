<?php
namespace TTG;

class Files extends \finfo
{
    const MAX_FILE_SIZE = 1024 * 1024 * 8;
    const ERROR_CODES = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.', # Introduced in PHP 5.0.3.
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.', # Introduced in PHP 5.1.0.
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.' # Introduced in PHP 5.2.0.
    ];

    private int $errorNum;
    private string $errorStr;
    private ?string $filePath = NULL;
    private ?int $fileSize = NULL;

    /**
     * File Upload with File Info
     * 
     * @param int $options - One or disjunction of more Fileinfo constants
     * @param string $magic_files - Location of the Magic Database File
     * 
     */
    public function __construct(int $options = FILEINFO_MIME_TYPE, string $magic_file = NULL)
    {
        return parent::__construct($options, $magic_file);
    }

    public function isOk(string $name, string $type = 'image'): bool
    {
        if (empty($_FILES[$name]['name']))
        {
            $this->errorNum = 0;
            $this->errorStr = "No file uploaded.";
            return false;
        }

        if (empty($_FILES[$name]['tmp_name']))
        {
            $this->errorNum = -1;
            $this->errorStr = "No file uploaded.";
            return false;
        }

        if (!is_uploaded_file($_FILES[$name]['tmp_name']))
        {
            $this->errorNum = -2;
            $this->errorStr = "This file was not uploaded by a POST / PUT request; Nice try.";
            return false;
        }

        // Is is now OK to check the actual file for information;

        if ($_FILES[$name]['error'] == UPLOAD_ERR_FORM_SIZE)
        {
            $this->errorNum = -3;
            $this->errorStr = "The uploaded file exceeds the MAX_FILE_SIZE of the Files class.";
            return false;
        }

        if ($type !== false AND substr($this->file($_FILES[$name]['tmp_name']), 0, strlen($type)) !== $type)
        {
            $this->errorNum = -4;
            $this->errorStr = "The uploaded file is not of an {$type} type.";
            return false;
        }

        if ($_FILES[$name]['error'] !== UPLOAD_ERR_OK)
        {
            $this->errorNum = $_FILES[$name]['error'];
            $this->errorStr = self::ERROR_CODES[$_FILES[$name]['error']];
            return false;
        }

        $this->fileSize = $_FILES[$name]['size'];

        return true;
    }

    public function getError(): string
    {
        return $this->getErrorNum() . ': ' . $this->getErrorStr();
    }

    public function getErrorNum(): int
    {
        return $this->errorNum;
    }

    public function getErrorStr(): string
    {
        return $this->errorStr;
    }

    public function moveUpload(string $name, string $path = '/images/profile/'): bool
    {
        $ext = explode('/', $_FILES[$name]['type'])[1];
        $hash = sha1_file($_FILES[$name]['tmp_name']);

        $this->filePath = $path . $hash . '.' . $ext;

        return move_uploaded_file(
            $_FILES[$name]['tmp_name'],
            $_SERVER['DOCUMENT_ROOT'] . $this->filePath
        );
    }

    public function moveSafeUpload(string $name, string $path): bool
    {
        $this->filePath = $path . $_FILES[$name]['name'];

        $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] . $this->filePath;

        if (!is_numeric($_POST['year']))
        {
            echo 'Year is not an int.' . PHP_EOL;
            return false;
        }

        if (!is_numeric($_POST['month']))
        {
            echo 'Month is not an int.' . PHP_EOL;
            return false;
        }

        if (!is_numeric($_POST['day']))
        {
            echo 'Day is not an int.' . PHP_EOL;
            return false;
        }

        // Make the dir tree.
        $TREE = explode('/', $DOCUMENT_ROOT);
        array_shift($TREE);
        array_pop($TREE);

        $pathNotWriteable = false;
        for ($PATH = '/', $i = 0, $j = count($TREE); $i < $j; ++$i)
        {
            $PATH .= "{$TREE[$i]}/";

            if (!file_exists($PATH) OR !is_dir($PATH))
            {
                if (!mkdir($PATH, 0755, true))
                {
                    echo "\n\nFailed to create directory {$PATH}\n\n";
                }
            }

            // One of those areas where rust Result type would be useful.
            if (!is_writable($PATH)) {
                $pathNotWriteable = true;
                $pathReason = "IS NOT WRITABLE: {$PATH}\n";
            } else {
                $pathNotWriteable = false;
            }
        }

        if ($pathNotWriteable)
        {
            echo $pathReason;
            return false;
        }

        return move_uploaded_file(
            $_FILES[$name]['tmp_name'],
            $_SERVER['DOCUMENT_ROOT'] . $this->filePath
        );
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function isJPEG(string $name, int $w = NULL, $h = NULL): bool
    {
        if ($_FILES[$name]['type'] != 'image/jpeg')
        {
            return false;
        }

        if (!function_exists('exif_imagetype'))
        {
            if (exif_imagetype($_FILES[$name]['tmp_name']) != IMAGETYPE_JPEG)
            {
                return false;
            }
        }

        if (NULL !== $w AND NULL !== $h)
        {
            [$width, $height, $type, $attr] = getimagesize($_FILES[$name]['tmp_name']);
            if ($width !== $w OR $height !== $h)
            {
                return false;
            }
        }

        return true;
    }

    public function isPDF(string $name): bool
    {
        if ($_FILES[$name]['type'] !== 'application/pdf')
        {
            return false;
        }

        if ($this->file($_FILES[$name]['tmp_name'], FILEINFO_MIME_TYPE) !== 'application/pdf')
        {
            return false;
        }

        return true;
    }
}
