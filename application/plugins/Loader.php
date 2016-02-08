<?php

/**
 * Default_Plugin_Loader
 *
 * Класс для работы с массивами
 *
 * @author naspeh
 * @version $Id: Loader.php 668 2008-06-30 10:01:20Z naspeh $
 * @see http://zendframework.ru/articles/class-autoload-zend-framework
 * @uses
 * @package    Module-Default
 * @subpackage Plugins
 */
require_once 'Zend/Loader/Autoloader.php';

class Default_Plugin_Loader {

    /**
     * Директория конфигураций
     *
     */
    const SUFFIX_CONFIGS = 'configs';

    /**
     * Директория контроллеров
     *
     */
    const SUFFIX_CONTROLLERS = 'controllers';

    /**
     * Директория моделей
     *
     */
    const SUFFIX_MODELS = 'models';

    /**
     * Директория представлений
     *
     */
    const SUFFIX_VIEWS = 'views';

    /**
     * Модуль по умолчанию
     *
     */
    const MODULE_DEFAULT = 'core';

    /**
     * Стек: префикс - путь
     *
     * @var array
     */
    protected static $_prefixToPaths = null;

    /**
     * Стек: модуль - путь
     *
     * @var array
     */
    protected static $_moduleToPaths = array();

    /**
     * Директория модулей
     *
     * @var string
     */
    protected static $_moduleDir;

    /**
     * Добавляет пару: префикс - путь
     *
     * @param string $path
     * @param string $prefix
     */
    public static function addPrefixToPath($path, $prefix) {
        $path = (string) $path;
        $prefix = rtrim($prefix, '_') . '_';
        if (is_dir($path)) {
            self::$_prefixToPaths[$prefix][] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        } else {
            throw new Exception('pathNotDir# ' . $path);
        }
    }

    /**
     * Добавляет директорию
     *
     * @param string $path
     * @param string $suffixToModels
     */
    public static function addDir($path, $suffixToModels = null) {
        try {
            $dir = new DirectoryIterator($path);
        } catch (Exception $e) {
            require_once 'Exception.php';
            throw new Exception('dirNotReadable# Directory: "' . $path . '" can not be read');
        }
        foreach ($dir as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }
            $prefix = $file->getFilename();
            require_once 'Zend/Filter.php';
            require_once 'Zend/Filter/Word/DashToCamelCase.php';
            $chain = new Zend_Filter();
            $chain->addFilter(new Zend_Filter_Word_DashToCamelCase());
            $prefix = $chain->filter($prefix);
            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $prefix) || ('CVS' == $prefix)) {
                continue;
            }
            $modelsDir = $file->getPathname();
            if (isset($suffixToModels)) {
                $modelsDir .= DIRECTORY_SEPARATOR . $suffixToModels;
            }
            if (is_dir($modelsDir)) {
                self::addPrefixToPath($modelsDir, $prefix);
            }
        }
    }

    /**
     * Ищет файл с классом, если находит, то подключает его(include_once) 
     *
     * @param string $class
     */
    public static function loadClass($class) {
        $class = (string) $class;
        if (class_exists($class)) {
            return;
        }
        $paths = self::getPrefixToPath();
        foreach ($paths as $prefix => $paths) {
            if (preg_match('/^' . $prefix . '(\w+)/', $class, $matches)) {
                $fileEnd = $matches[1];
                $fileEnd = str_replace('_', '/', $fileEnd) . '.php';
                foreach ($paths as $path) {
                    $file = $path . $fileEnd;
                    if (Zend_Loader::isReadable($file)) {
                        include_once $file;
                        if (!class_exists($class, false) && !interface_exists($class, false)) {
                            require_once 'Exception.php';
                            throw new Exception('classNotExists');
                        }
                        return;
                    }
                }
            }
        }
        require_once 'Exception.php';
        throw new Exception('fileNotLoad');
    }

    /**
     * Возвращает стек или элемент стека
     *
     * @param string $prefix
     * @return array|string|false
     */
    public static function getPrefixToPath($prefix = '') {
        $prefix = (string) $prefix;
        if (empty($prefix)) {
            return self::$_prefixToPaths;
        } elseif (isset(self::$_prefixToPaths[$prefix])) {
            return self::$_prefixToPaths[$prefix];
        } else {
            return false;
        }
    }

    /**
     * Регистрирует автолоадер
     *
     * @param boolean $enabled 
     */
    public static function registerAutoload($enabled = true) {
//        Zend_Loader::registerAutoload(get_class(new self()), $enabled);
        $class = get_class(new self());
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);

        if ('Zend_Loader' != $class) {
            self::loadClass($class);
            $methods = get_class_methods($class);
            if (!in_array('autoload', (array) $methods)) {
                require_once 'Zend/Exception.php';
                throw new Zend_Exception("The class \"$class\" does not have an autoload() method");
            }

            $callback = array($class, 'autoload');

            if ($enabled) {
                $autoloader->pushAutoloader($callback);
            } else {
                $autoloader->removeAutoloader($callback);
            }
        }
    }

    /**
     * spl_autoload() suitable implementation for supporting class autoloading.
     *
     * @param string $class
     * @return string|false Class name on success; false on failure
     */
    public static function autoload($class) {
        try {
            self::loadClass($class);
            return $class;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Удаляет пару префикс - путь
     *
     * @param string $prefix
     * @return boolean
     */
    public static function removePrefixToPath($prefix = '') {
        $prefix = (string) $prefix;
        if (empty($prefix)) {
            self::$_prefixToPaths = null;
        } elseif (isset(self::$_prefixToPaths[$prefix])) {
            unset(self::$_prefixToPaths[$prefix]);
        } else {
            return false;
        }
        return true;
    }

    /**
     * Добаляет связку "имя модуля" => "путь к паке с модулем"
     *
     * @param string $module
     * @param string $path
     * @return Core_Module
     */
    public static function addModuleToPath($path, $module) {
        $module = (string) $module;
        $path = (string) $path;
        if (is_dir($path)) {
            self::$_moduleToPaths[$module] = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
        } else {
            require_once 'Exception.php';
            throw new Exception('pathNotDir# ' . $path);
        }
    }

    /**
     * Устанавливает директорию с модулями.
     *
     * @param string $path
     * @return string
     */
    public static function setModuleDir($path) {
        try {
            $dir = new DirectoryIterator($path);
        } catch (Exception $e) {
            require_once 'Exception.php';
            throw new Exception('dirNotReadable# Directory: "' . $path . '" can not be read');
        }
        self::clearModuleDir();
        self::$_moduleDir = $dir->getPath() . DIRECTORY_SEPARATOR;
        foreach ($dir as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }
            $module = $file->getFilename();
            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
                continue;
            }
            $moduleDir = $file->getPathname();
            self::addModuleToPath($moduleDir, $module);
        }
        return self::$_moduleDir;
    }

    /**
     * Возвращает установленную директорию с модулями.
     *
     * @return string
     */
    public static function getModuleDir() {
        return self::$_moduleDir;
    }

    /**
     * Очищает установленную директорию с модулями.
     *
     * @return true
     */
    public static function clearModuleDir() {
        self::$_moduleDir = null;
        self::$_moduleToPaths = array();
        return true;
    }

    /**
     * Возвращает связку "имя модуля" => "путь к паке с модулем", все если $module = '' 
     *
     * @param string $module
     * @return mixed
     */
    public static function getModuleToPath($module = '') {
        $module = (string) $module;
        if (empty($module)) {
            return self::$_moduleToPaths;
        } elseif (isset(self::$_moduleToPaths[$module])) {
            return self::$_moduleToPaths[$module];
        } else {
            return false;
        }
    }

}
