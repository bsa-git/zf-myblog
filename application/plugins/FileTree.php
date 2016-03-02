<?php

/**
 * Default_Plugin_FileTree.php
 *
 * Plugin - tools formanipulating file trees
 * @author Colin Mckinnon
 *
 * NB manipulating large trees of files can quickly fill up available memory - and
 * this class makes deleting lots of files in a single go very easy - please be
 * careful
 */
class Default_Plugin_FileTree {

// ------------------- properties --------------
    /**
     * @var string - canonical path this object refers to
     * @access protected
     */
    var $baseDir;

    /**
     * read-only
     * @var integer - number of files affected by the last operation
     * @access public - read only
     */
    var $opsCompleted;

    /**
     * config setting
     * normally if an action cannot be carried on a file/subdir
     * it will cause the current recusrion to stop - setting this
     * to true will make the recursion press on with the files it
     * is able to access
     * @var bool 
     * @access public
     */
    var $ignoreNoDescend;

    /**
     * config setting
     * @var bool
     * @access public
     */
    var $caseSensitive;

    /**
     * config setting - amount of memory used to stop processing (as a ratio)
     * @var float
     * @access public
     */
    var $maxMemRatio;

    /**
     * config settings - how long to wait between memory checks
     * @var integer
     * @access public
     */
    var $memFreq;

    /**
     * because call_user_func does not support pass-by-reference we keep
     * any data at the object level
     */
    var $data;

    /**
     * populated when something unplanned happenned
     * @var string
     * @access public
     */
    var $error;

    /**
     * user callbacks - these add additional attributes to files
     * populated by addCallback
     * @var mixed
     * @access protected
     */
    var $userFn;

    /**
     * Protocol - (HTTP, FTP)
     * @var string
     * @access protected
     */
    var $is_ftp = false;

// -------------------- methods ------------------------
    /**
     * @param string baseDir - directory to consider as tree root
     *
     * Constructor
     */
    function __construct($baseDir) {
        $this->ignoreNoDescend = false;
        $this->caseSensitive = true;
        if (stristr(PHP_OS, 'WIN')) {
            $this->caseSensitive = TRUE;
        }
        $this->data = array();

        // Определим протокол 
        $arrBaseDir = explode("://", $baseDir);
        if (count($arrBaseDir) > 1) {
            $protocol = strtolower($arrBaseDir[0]);
            $this->is_ftp = $protocol == "ftp";
        }

        $this->baseDir = $this->fixUpRoot($baseDir);
        $this->userFn = array();
        $this->maxMemRatio = 0.9;
        $this->memFreq = 50;
    }

    /**
     * @access protected
     * @param string treeRoot - path to base directory
     * @return mixed - false if failed, string otherwise
     *
     * utility method
     */
    protected function fixUpRoot($treeRoot) {
        if (!$this->is_ftp) {
            $treeRoot = realpath($treeRoot);
        }
        if (is_dir($treeRoot)) {
            if (substr($treeRoot, -1, 1) !== '/') {
                $treeRoot.='/';
            }
        } else {
            $this->error = $treeRoot . "is not a directory";
            return false;
        }
        return $treeRoot;
    }

    /**
     * @access public
     *
     * clear the data stored for the current fileTree
     */
    public function clear() {
        clearstatcache();
        $this->opsCompleted = count($this->data);
        $this->data = array();
        $this->error = '';
    }

    //----------------- Пользовательские ф-ии -----------//

    /**
     * @access public
     * @param mixed $cb - a callback - either a function name or an array($obj, 'method')
     *
     * callBacks are applied to decorate each file read with additional attributes
     * called with 1 argument:
     * canonical path
     * and should return an array with the attribute(s) as key(s)
     * and the corresponding values
     */
    public function addCallback($cb) {
        $this->userFn[] = $cb;
    }

    /**
     * @access public
     */
    public function delCallBack($cb) {
        foreach ($this->userFn as $key => $val) {
            if ($val === $cb) {
                unset($this->userFn[$key]);
                // don't break - there may be duplicates
            }
        }
    }

    /**
     * @param mixed $callback - string (function name) or array (object, method) to apply
     * @param mixed $arg - static data passed to callback - changes are lost
     * @return bool the value returned by the callback
     *
     * Note that the recursion ends when the callback returns false
     * the first parameter passed to $callback is the canonical file path, the 
     * second is $arg
     * It is not possible to call-by-reference with call_user_func() in a non-
     * deprecated manner
     * note that if you want to modify the dataset then you need to include a 
     * **reference** to this pfpFileTree instance within arg
     * Note also that this function returns a boolean - not the current data array
     */
    function applyCallback($callback, $arg = false) {
        $this->opsCompleted = 0;
        $this->error = '';
        foreach ($this->data as $effPath => $attrs) {
            $this->opsCompleted++;
            if (!call_user_func($callback, $this->baseDir . $effPath, $attrs, $arg)) {
                return false;
            }
        }
        return true;
    }

    //------------- Создание дерева файлов -----------//

    /**
     * @access public
     * @param array filter - optional pattern to apply to files
     * @return array - key is path relative to basedir, value is array of attributes
     *
     * Note that this function creates an entry point into a reseable recursion which 
     * uses a callback to perfrom required action on files
     * i.e. will only be called once usually
     * See readTreeStat method for attributes
     * 
     * The filter array is an array with attrbutes as key names
     * and conditions as values.        
     * Integer values (times and sizes) can be prefixed by
     * + or > to extract files with a value greater than passed
     * or prefixed by - or < to extract files with a value less
     * otherwise those with an exact match or returned
     *
     * e.g.
     * filter( array(
     *            'name'=>'*.png',
     *            'size' => '>4096',
     *            'w' => false));
     * will strip out everything except files with a .png extension
     * and size greater than 4k which are not writeable
     */
    public function readTree($filter = array()) {
        $this->error = '';
        $d = $this->baseDir;
        $this->opsCompleted = 0;
        $this->error = '';

        if ($this->is_ftp) {
            return $this->readTree_ftp($filter);
        } else {
            $callback = array($this, 'readTreeWorker');
            $ret = $this->runRecursion($callback, $this->baseDir, $filter);
            if ($ret === true)
                return $this->data;
        }
        return false;
    }

    /**
     * @access public
     * @param array filter - optional pattern to apply to files
     * @return array - key is path relative to basedir, value is array of attributes
     *
     * Note that this function creates an entry point into a reseable recursion which 
     * uses a callback to perfrom required action on files
     * i.e. will only be called once usually
     * See readTreeStat method for attributes
     * 
     * The filter array is an array with attrbutes as key names
     * and conditions as values.        
     * Integer values (times and sizes) can be prefixed by
     * + or > to extract files with a value greater than passed
     * or prefixed by - or < to extract files with a value less
     * otherwise those with an exact match or returned
     *
     * e.g.
     * filter( array(
     *            'name'=>'*.png',
     *            'size' => '>4096',
     *            'w' => false));
     * will strip out everything except files with a .png extension
     * and size greater than 4k which are not writeable
     */
    public function readTree_ftp($filter = array()) {
        $files = array();
        // Открыть заведомо существующий каталог и начать считывать его содержимое
        if (is_dir($this->baseDir)) {
            $dh = opendir($this->baseDir);
            if ($dh) {
                while (($file = readdir($dh)) !== false) {
                    $files[] = $file;
                }
                closedir($dh);
            }
        }

        foreach ($files as $value) {
            $this->opsCompleted++;
            $value = new Default_Plugin_String($value);
            $value = (string) $value->Strip();
            $fileInfo = explode(" ", $value);
            $index = count($fileInfo) - 1;
            $effPath = $fileInfo[$index];
            $this->data[$effPath] = $this->readTreeStat_ftp($fileInfo);

            if (!$this->filterFile($effPath, $filter)) {
                unset($this->data[$effPath]);
                $this->opsCompleted--;
            }
        }
        return $this->data;
    }

    /**
     * This does the recursion on behalf of all the other public methods
     * Note that recursion runs depth first 
     */
    function runRecursion($callback, $dir, $arg) {

        //---------------------
        $dh = opendir($dir);
        if (!$dh && $this->ignoreNoDescend) {
            return true;
        }
        if (!$dh) {
            $this->error = 'unable to read dir ' . dir;
            return false;
        }
        while ($f = readdir($dh)) {

            if (!$this->checkMem())
                return false;
            if (($f == '.') || ($f == '..'))
                continue;
            if (is_dir($dir . $f)) {
                if (!$this->runRecursion($callback, $dir . $f . '/', $arg)) {
                    return false;
                }
            }

            if (!call_user_func($callback, $dir . $f, $arg)) {
                return false;
            }
        }
        closedir($dh);
        return true;
    }

    /**
     * @access protected
     */
    protected function readTreeWorker($f, $filter) {
        $this->opsCompleted++;
        $effPath = substr($f, strlen($this->baseDir));
        $this->data[$effPath] = $this->readTreeStat($f);
        if (!$this->filterFile($effPath, $filter)) {
            unset($this->data[$effPath]);
            $this->opsCompleted--;
        }
        return true;
    }

    /**
     * @access protected
     * @param string $f - canonical file path
     * @return array of attributes
     *
     * Attributes are:
     *  'exists': bool
     *  'size': in bytes
     *  'mtime': timestamp when file last modified
     *  'type': f/d/o (file/directory/other)
     *  'w': bool - is the file writeable
     *  'r': bool - is the file readable
     */
    protected function readTreeStat_ftp($fileInfo = array()) {
        $out = array();
        $nowZendDate = new Zend_Date();
        $nowYear = $nowZendDate->get(Zend_Date::YEAR_8601);
        //-------------------------
        // 07-04-13 09:13AM 689 000052_130704_000000DM.DAR
        // Найдем инф. о файле
        $out['exists'] = TRUE;
        $out['size'] = $fileInfo[2];

        // Определим время создания файла
        $arrDate = explode("-", $fileInfo[0]);
        if (count($arrDate) == 3) {
            if (strlen($arrDate[2]) == 2) {
                $fDate = "{$arrDate[0]}-{$arrDate[1]}-{$nowYear[0]}{$nowYear[1]}{$arrDate[2]}";
            } else {
                $fDate = "{$arrDate[0]}-{$arrDate[1]}-{$arrDate[2]}";
            }
        }
        $fTime = "{$fDate} {$fileInfo[1]}";
        $zTime = new Zend_Date($fTime, null, "en_US");
        $timestamp = $zTime->get(Zend_date::TIMESTAMP);
        $fTime = $zTime->get(Zend_date::ISO_8601);
        $out['mtime'] = $timestamp;
        $arrDateTime = explode(" ", date('Y-m-d H:i:s', $timestamp));
        $out['sdate'] = $arrDateTime[0];
        $out['stime'] = $arrDateTime[1];
        return $out;
    }

    /**
     * @access protected
     * @param string $f - canonical file path
     * @return array of attributes
     *
     * Attributes are:
     *  'exists': bool
     *  'size': in bytes
     *  'mtime': timestamp when file last modified
     *  'type': f/d/o (file/directory/other)
     *  'w': bool - is the file writeable
     *  'r': bool - is the file readable
     */
    protected function readTreeStat($f) {
        $out = array();
        //-----------------

        $out['exists'] = file_exists($f);
        $out['size'] = filesize($f);
        $out['mtime'] = filemtime($f);
        $arrDateTime = explode(" ", date('Y-m-d H:i:s'));
        $out['sdate'] = $arrDateTime[0];
        $out['stime'] = $arrDateTime[1];

        if (is_dir($f)) {
            $out['type'] = 'd';
        } else if (is_file($f) || is_link($f)) {
            $out['type'] = 'f';
        } else {
            $out['type'] = 'o';
        }
        // the is_ fns return 1 or null on my installation???!!!!
        $out['w'] = is_writable($f) ? true : false;
        $out['r'] = is_readable($f) ? true : false;
        foreach ($this->userFn as $callback) {
            $out = array_merge($out, call_user_func($callback, $f));
        }
        return $out;
    }

    //--------------- Удаление файлов ---------//

    /**
     * @access public
     * @return bool - true if successful
     *
     * $this->data no longer contains the filetree after this operatiion
     * NB $this->data is not updated automatically - to find the current
     * state of the files:
     *     $this->clear();
     *     $this->readTree()
     */
    public function delFiles() {
        $this->opsCompleted = 0;
        $this->error = '';
        //if ($this->canDelete()) {
        if ($this->canDelFiles()) {
            $this->sortFiles(array('name' => '-'));
            // ^ since we want delete cotents of a dir before the dir
            foreach ($this->data as $f => $k) {
                $exists = isset($this->data[$f]['exists']) ? $this->data[$f]['exists'] : FALSE;
                //if (!$this->data[$k]['exists'])
                if (!$exists) {
                    continue;
                }
                $file = $this->baseDir . $f;
                if (is_file($file) && @unlink($file)) {
                    $this->opsCompleted++;
                    $this->data[$f]['exists'] = false;
                    continue;
                } else if (is_dir($file) && @rmdir($file)) {
                    $this->opsCompleted++;
                    $this->data[$f]['exists'] = false;
                    continue;
                }
                // not bothered about other file types
            }
        } else {
            $this->error = "Permissions do not allow forall files to be deleted";
            return false;
        }
        return true;
    }

    /**
     * @access public
     * @return bool - true if files can be deleted
     *
     * checks if all the files currently referenced in $data can be
     * deleted
     */
    public function canDelFiles() {
        foreach ($this->data as $k => $f) {
            $this->canDelWorker($this->baseDir . $k);
        }
        foreach ($this->data as $k => $f) {
            if ($this->data[$k]['can_d'] === false)
                return false;
        }
        return true;
    }

    /**
     * @access protected
     */
    protected function canDelWorker($file) {
        $effPath = substr($file, strlen($this->baseDir));
        if (!$this->data[$effPath]['exists']) {
            // already deleted
            $this->data[$effPath]['can_d'] = true;
            return true;
        }
        if (is_writeable(dirname($file))) {
            $this->data[$effPath]['can_d'] = true;
            return true;
        } else {
            $this->data[$effPath]['can_d'] = false;
        }
        return true;
    }

    //--------------- Сравнение файлов из разных директорий --------//

    /**
     * @param string $otherTree
     * @return array of relative filepaths with a status
     * 
     * returns an array describing the existence of files with
     * the same path relative to the other root
     * Note that files in the other tree which are not
     * listed in this tree are ignored
     *
     * status of files is:
     * 0 if same in both trees
     * 1 if this is different and newer, 
     * -1 if this is different and older
     * 2 if not exists in other
     * 3 if type mismatch (file/dir)
     */
    function compareTree($otherTree) {
        $otherTree = $this->fixUpRoot($otherTree);
        if ($otherTree === $this->baseDir) {
            $this->error = "Other tree is same as current";
            return false;
        }
        if ($otherTree === false)
            return false;
        $this->opsCompleted = 0;
        $this->error = '';
        $callback = array($this, 'compareTreeWorker');
        $ret = $this->runRecursion($callback, $this->baseDir, $otherTree);
        if ($ret === true) {
            return $this->data;
        }
        return false;
    }

    /**
     * @access protected
     */
    protected function compareTreeWorker($file, $otherRoot) {
        $effPath = substr($file, strlen($this->baseDir));
        if (!file_exists($otherRoot . $effPath)) {
            $this->data[$effPath]['cmp'] = 2;
            return true;
        }
        if (is_dir($otherRoot . $effPath)) {
            $type = 'd';
        } else if (is_file($otherRoot . $effPath) || is_link($otherRoot . $effPath)) {
            $type = 'f';
        } else {
            $type = 'o';
        }
        if ($type != $this->data[$effPath]['type']) {
            $this->error = "file type mismatch for $effPath";
            $this->data[$effPath]['cmp'] = 3;
            return false;
        }
        if ($type == 'd') {
            // only the names need to match for dirs
            $this->data[$effPath]['cmp'] = 0;
            return true;
        }
        if (filesize($this->baseDir . $effPath) == filesize($otherRoot . $effPath)) {
            $this->data[$effPath]['md5'] = md5_file($this->baseDir . $effPath);
            if ($this->data[$effPath]['md5'] == md5_file($otherRoot . $effPath)) {
                $this->data[$effPath]['cmp'] = 0;
                return true;
            }
        }
        if (filemtime($this->baseDir . $effPath) > filemtime(otherRoot . $effPath)) {
            $this->data[$effPath]['cmp'] = 1;
            return true;
        } else {
            $this->data[$effPath]['cmp'] = -1;
            return true;
        }
    }

    //------------- Копирование файлов из одной директории в другую ----//

    /**
     * @access public
     * @param string $otherTree - root of locaton to write current tree to
     * @return mixed - false if one or more files not writeable
     *
     * Copy the current fileset to another location
     */
    public function writeTo($otherTree) {
        $otherTree = $this->fixupRoot($otherTree);
        if (!$this->canWriteTo($otherTree)) {
            return false;
        }
        foreach ($this->data as $k => $f) {
            if (!$this->data[$k]['exists'])
                continue;
            if (!$this->writeToWorker($k, $f, $otherTree)) {
                // this should never hapen?
                return $false;
            }
        }
        return $this->data;
    }

    /**
     * @access protected
     */
    protected function writeToWorker($effPath, $attrs, $other) {
        if ($attrs['type'] == 'o') {
            $this->error = "Can only process files and directories";
            return false;
        }
        $dir = dirname($other . $effPath);
        if ($attrs['type'] == 'd') {
            $dir = $other . $effPath;
        }
        if (!is_dir($dir)) {
            if (!mkdir($dir, umask(), true)) {
                // this should never happen
                $this->error = "Failed to create directory $dir";
                return false;
            }
        }
        if ($attrs['type'] == 'f') {
            // need to copy file to!
            if (!copy($this->baseDir . $effPath, $other . $effPath)) {
                // this should not hapen
                $this->error = "Copy failed for $effPath";
                return false;
            }
        }
        return true;
    }

    /**
     * @access public
     * @param string $otherTree - root of locaton to write current tree to
     * @return bool - false if one or more files not writeable
     *
     * test if the tree can be written to another location
     */
    public function canWriteTo($otherTree) {
        $otherTree = $this->fixUpRoot($otherTree);
        if ($otherTree === false)
            return false;
        if ($otherTree === $this->baseDir) {
            $this->error = "other directory is same as current";
            return false;
        }
        foreach ($this->data as $k => $attrs) {
            $this->canWriteToWorker($k, $otherTree);
        }
        foreach ($this->data as $k => $f) {
            if ($this->data[$k]['can_w'] === false)
                return false;
        }
        return true;
    }

    /**
     * @access protected 
     */
    protected function canWriteToWorker($effPath, $otherRoot) {
        if (!$this->data[$effPath]['exists']) {
            // we treat it as if we can write
            $this->data[$effPath]['can_w'] = true;
            return true;
        }
        if ($this->data['type'] == 'o') {
            // not going to try to deal with links, devices, sockets
            $this->error = "Can only process files and directories";
            $this->data[$effPath]['can_w'] = false;
            return false;
        }
        $try = $otherRoot . $effPath;
        while (strlen($try) + 1 > strlen($otherRoot) && !file_exists($try)) {
            $try = dirname($try);
        }
        $this->data[$effPath]['can_w'] = is_writeable($try);
        return true;
    }

    //-------- Фильтрация файлов в директории -------//

    /**
     * @access public
     * @param array $attr - set of attributes to match (see below)
     * @return array - data after applying filter
     *
     * Filter the current data set based on an array of filters
     * Integer values (times and sizes) can be prefixed by 
     * + or > to extract files with a value greater than passed
     * or prefixed by - or < to extract files with a value less
     * otherwise those with an exact match or returned
     *
     * e.g.
     * filter( array(
     *            'name'=>'*.png',
     *            'size' => '>4096',
     *            'w' => false));
     * will strip out everything except files with a .png extension
     * and size greater than 4k which are not writeable
     */
    public function filter($attr) {
        $initcount = count($this->data);
        foreach ($this->data as $effPath => $dat) {
            if (!$this->filterFile($effRoot, $attr)) {
                unset($this->data[$effPath]);
            }
        }
        $this->opsCompleted = $initcount - count($this->data);
        return $this->data;
    }

    /**
     * @access protected
     * @param string $file - canonical file name
     * @param array $filter
     * @return bool - true if selected by filter
     */
    protected function filterFile($file, $filter) {
        foreach ($filter as $cmp_k => $cmp_v) {
            if (!@$this->filterAttr($file, $cmp_k, $cmp_v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @access protected
     * @param string $path - the current path being checked
     * @param string $key - the attribute to check
     * @param mixed $val - the value to compare against
     * @return bool - true if the data for the path matches the key/val
     * false if it does not match or the attribute is not populated
     */
    protected function filterAttr($path, $key, $val) {
        if ($key == 'name') {
            return fnmatch($val, $path, ($this->caseSensitive ? FNM_CASEFOLD : 0));
        }
        if (!array_key_exists($key, $this->data[$path])) {
            return false;
        }
        switch (substr($val, 0, 1)) {
            case '>':
            case '+':
                if ($this->data[$path][$key] <= (integer) substr($val, 1)) {
                    return false;
                }
                break;
            case '<':
            case '-':
                if ($this->data[$path][$key] >= (integer) substr($val, 1)) {
                    return false;
                }
                break;
            case '!':
                if ($this->data[$path][$key] == substr($val, 1)) {
                    return false;
                }
                break;
            default:
                if ($val === 'true')
                    $val = true;
                if ($val === 'false')
                    $val = false;
                if ($this->data[$path][$key] != $val) {
                    return false;
                }
                break;
        }
        return true;
    }

    //------------- Сортировка файлов в директории ------//

    /**
     * @access public
     * @param array $attr - keys are attributes to sort on, a value of '-' sorts descending
     *
     * note that the order if entries in the array determines their priority in sorting
     * eg
     * sortFiles(array('size'=>'+','mtime'=>'-'));
     * small files appear first (ascending), if 2 files have the same size, the  
     * newest appears first (descending)
     */
    public function sortFiles($attr) {
        $this->sortAttr = $attr;
        if (count($attr)) {
            if (!uksort($this->data, array($this, 'sortWorker'))) {
                $this->error = "Sort failed for unknown reason";
                return false;
            }
        }
        return $this->data;
    }

    /**
     * @access protected
     */
    protected function sortWorker($a, $b) {
        foreach ($this->sortAttr as $key => $mode) {
            if ($key == 'name') {
                // we are sorting using the keys
                if ($this->caseSensitive) {
                    $cmp = strcasecmp($a, $b) * ($mode == '-' ? -1 : 1);
                } else {
                    $cmp = strncasecmp($a, $b) * ($mode == '-' ? -1 : 1);
                }
                if ($cmp)
                    return $cmp;
            }
            // sorting using using attributes
            if ($this->data[$a][$key] === $this->data[$b][$key]) {
                // same - try sorting by next attribute
                continue;
            }
            if ($this->data[$a][$key] > $this->data[$b][$key]) {
                return ($mode == '-' ? -1 : 1);
            }
            return ($mode == '-' ? 1 : -1);
        }
        return 0;
    }

    //-------------- Доп. ф-ии -----------//

    /**
     * @access public
     * @param array $attr
     * @return object - new instance of $this
     *
     * like filter -but this leaves the current dataset intact
     * and returns a new (filtered) instance of this class
     */
    public function subset($attr) {
        $result = clone $this;
        $result->filter($attr);
        return $result;
    }

    /**
     * @access public
     * @return bool
     *
     * uses the maxMemRatio an memFreq variables to assess
     * current memory usage - to prevent ugly errors when its full
     *
     * memFreq determines the number of files read between full memory checks
     * maxMemRatio is the ratio of used to total memory - above which
     * processing should stop
     */
    public function checkMem() {
        static $iterations;
        static $mlimit;

        if (!$mlimit) {
            $mlimit = ini_get('memory_limit');
            $mul = 1;
            switch (substr($mlimit, -1)) {
                case 'G':
                    $mul*=1024; // no break deliberate
                case 'M':
                    $mul*=1024;
                case 'K':
                    $mul*=1024;
                    $mlimit = $mul * (integer) $mlimit;
                    break;
                default:
                    $mlimit = (integer) $mlimit;
                    break;
            }
            if ($mlimit <= 0) {
                $mlimit = 4294967296; // surely big enuf?
            }
        }

        if ($this->memFreq > $iterations) {
            $iterations++;
            return true;
        }
        $usedRatio = memory_get_usage() / $mlimit;
        $iterations = 0;
        if ($this->maxMemRatio < $usedRatio) {
            $this->error = 'Memory usage at ' . $usedRatio . '%';
            return false;
        }
        return true;
    }

    /**
     * @access public
     * @param bool $print
     * @return string|void
     * 
     * utility function to print the files and attributes measured
     */
    public function ls($print = true) {
        $result = "";
        if ($print) {
            $result = "ls print:\n";
        }
        foreach ($this->data as $k => $f) {
            $result .= "$k";
            foreach ($this->data[$k] as $attr => $val) {
                $result .= ",$attr=>$val";
            }
            $result .= "\n";
        }
        if ($print) {
            print $result;
        } else {
            return $result;
        }
    }

}

// end class pfpFileTree
