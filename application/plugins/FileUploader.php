<?php

/**
 * Default_Plugin_FileUploader
 * 
 * Plugin - upload files
 *
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Plugins
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Default_Plugin_FileUploader {

    public $allowedExtensions = array();
    public $sizeLimit = 10485760;
    public $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760) {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);

        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false;
        }
    }

    private function checkServerSettings() {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {

            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';

            $errMsg = Default_Plugin_SysBox::Translate('увеличить значения в PHP переменных - post_max_size и upload_max_filesize до размера - ');
            throw new Exception($errMsg . $size);
        }
    }

    private function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    static function isFileUploader() {

        //------------------
        if (isset($_GET['qqfile'])) {
            $result = 'Xhr';
        } elseif (isset($_FILES['qqfile'])) {
            $result = 'Iframe';
        } else {
            $result = '';
        }
        return $result;
    }

    /*
     * Set new values for the PHP ini file
     *
     * @param  array $arrParams
     * @return void
     */

    static function iniSetConfig_PHP($arrResourse) {
        $sizeLimit = 0;
        $post_max_size = (int) ini_get('post_max_size');
        $upload_max_filesize = (int)  ini_get('upload_max_filesize');
        $memory_limit = (int) ini_get('memory_limit');
        //--------------------
        $config = Zend_Registry::get('config'); // uploader.image.maxsize
        // Find max sizeLimit for resourses
        foreach ($arrResourse as $resourse) {
            $maxsize = (int) $config['uploader'][$resourse]['maxsize'];
            $sizeLimit = ($maxsize > $sizeLimit) ? $maxsize : $sizeLimit;
        }
        if ($sizeLimit > $post_max_size) {
            // No set with this function (http://php.net/manual/ru/configuration.changes.modes.php)
            ini_set('post_max_size', $sizeLimit . 'M'); // Default=8M 
            $post_max_size = (int) ini_get('post_max_size');
        }
        // No set with this function (http://php.net/manual/ru/configuration.changes.modes.php)
        if ($sizeLimit > $upload_max_filesize) {
            ini_set('upload_max_filesize', $sizeLimit . 'M'); // Default=2M
            $upload_max_filesize = (int)  ini_get('upload_max_filesize');
        }
        if ($sizeLimit > $memory_limit) {
            ini_set('memory_limit', ($sizeLimit + $memory_limit) . 'M'); // Default=128M
            $memory_limit = (int) ini_get('memory_limit');
        }
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE) {
        if (!is_writable($uploadDirectory)) {
            return array('error' => Default_Plugin_SysBox::Translate('Ошибка сервера. В директорию загрузки - запрещена запись файлов.'));
        }

        if (!$this->file) {
            return array('error' => Default_Plugin_SysBox::Translate('Файлы не были загружены.'));
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => Default_Plugin_SysBox::Translate('Файл пустой.'));
        }

        if ($size > $this->sizeLimit) {
//            return array('error' => $this->file->getName() . ' ' .
//                    Default_Plugin_SysBox::Translate('файл имеет слишком большой размер, максимально допустимым размером файла является - ')) .
//                    $this->sizeLimit;
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            return array('error' => Default_Plugin_SysBox::Translate('файл имеет слишком большой размер, максимально допустимым размером файла является - ') . $size);
        }

        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => Default_Plugin_SysBox::Translate('Файл имеет недопустимое расширение. Оно должно быть одним из - ') . $these . '.');
        }

        if (!$replaceOldFile) {
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }

        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)) {
            return array('success' => true);
        } else {
            return array('error' => Default_Plugin_SysBox::Translate('Не удалось сохранить, загруженный файл. Была отменена загрузка или произошла ошибка сервера.'));
        }
    }

}

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        $getSize = $this->getSize();

        if ($realSize != $getSize) {
            return false;
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    function getName() {
        return $_GET['qqfile'];
    }

    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])) {
            return (int) $_SERVER["CONTENT_LENGTH"];
        } else {
            throw new Exception(Default_Plugin_SysBox::Translate('Длина, полученного содержимого слишком большая.'));
        }
    }

}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
            return false;
        }
        return true;
    }

    function getName() {
        return $_FILES['qqfile']['name'];
    }

    function getSize() {
        return $_FILES['qqfile']['size'];
    }

}
