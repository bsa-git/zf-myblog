<?php

/**
 * Admin_ToolsController
 *
 * Controller - Tools
 * Tools for the servicing site
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Admin (administration of site)
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class Admin_ToolsController extends Default_Plugin_BaseController {

    /**
     * Initialization controller
     *
     */
    public function init() {
        parent::init();
        $this->_breadcrumbs->addStep($this->Translate('Инструменты'), $this->getUrl(null, 'tools', 'admin'));
    }

    /**
     * Action - index
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/index
     * - /admin/tools
     *
     * @return void
     */
    public function indexAction() {
        
    }

    /**
     * Action - backup
     * create a backup copy of the database
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/backup
     *
     * @return void
     */
    public function backupAction() {
        $this->_breadcrumbs->addStep($this->Translate('Резервное копирование'));
        $this->view->message = $this->Translate('Раздел сайта находится в разработке').'!';
        $this->view->class_message = 'caution';
    }

    /**
     * Action - search
     * Search on site
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/search
     *
     * @return void
     */
    public function searchAction() {
        $this->_breadcrumbs->addStep($this->Translate('Поиск на сайте'));
    }

    /**
     * Action - listinfo
     * show information help
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/listinfo
     *
     * @return void
     */
    public function listinfoAction() {
        $this->_breadcrumbs->addStep($this->Translate('Список информационной помощи'));
    }
    
    /**
     * Action - loginfo
     * show event log
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/loginfo
     *
     * @return void
     */
    public function loginfoAction() {
        $this->_breadcrumbs->addStep($this->Translate('Журнал событий'));
    }
    
    /**
     * Action - errorinfo
     * show error log
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/errorinfo
     *
     * @return void
     */
    public function errorinfoAction() {
        $this->_breadcrumbs->addStep($this->Translate('Журнал ошибок'));
    }
    
    /**
     * Action - logstat
     * показать журнал статистики
     * show statistics log
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/logstat
     *
     * @return void
     */
    public function logstatAction() {
        $this->_breadcrumbs->addStep($this->Translate('Журнал статистики'));
    }

    /**
     * Action - phpinfo
     * get information about PHP and other system information
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/phpinfo
     *
     * @return void
     */
    public function phpinfoAction() {
        $params = $this->getRequest()->getParams();

        // Запомним текущий URL, чтобы использовать его в будущем
        // для Breadcrumbs
        $Zend_Auth = Zend_Registry::get("Zend_Auth");
        $Zend_Auth->lastBreadcrumbs = array(
            'module' => $params['module'],
            'controller' => $params['controller'],
            'action' => $params['action'],
            'title' => $this->Translate('Настройки PHP')
        );

        // Текущий Breadcrumbs
        $this->_breadcrumbs->addStep($this->Translate('Настройки PHP'));

        // Установим инф. об отчете для кнопок 'report-buttons.tpl'
        $this->view->url_pdf = '/tools/pdf';
        $this->view->name_pdf = 'phpinfo'; //phpinfo zend-progress tcpdf_test
        $this->view->url_content = '/tools/phpinfo';

        // Режим отчета
        if (isset($params['report'])) {
            $this->view->report = $params['report'];
        }
    }

    /**
     * Action - pdf
     * сreate a PDF document using the mPDF library
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/pdf
     *
     * @return void
     */
    public function pdfAction() {
        $isCommonFont = false;
        $pdfParams = array();
        //---------------------
        // Получим параметры
        $params = $this->getRequest()->getParams();
        $report = $params['name'];

        // Определим параметры для конвертации HTML в PDF
        switch ($report) {
            case 'phpinfo':
                // Установим параметры для отчета
                $html = Default_Plugin_SysBox::getPHPInfo();
                $pdfParams['pdfReport'] = $report;
                $pdfParams['html'] = $html;
                $pdfParams['isCommonFont'] = true;
                $pdfParams['pathStylesheet'] = 'css/report/phpinfo.css';
                $pdfParams['headerLeftMargin'] = 'Params of PHP';
                $pdfParams['headerCentreMargin'] = Default_Plugin_SysBox::getFullURL_Res('/images/system/settings32x32.png');
                $pdfParams['footerRightMargin'] = Default_Plugin_SysBox::getFullUrl_For_FilePDF($report);

                break;
            case 'zend-progress':

                $adapter = new Zend_ProgressBar_Adapter_JsPush(array(
                            'updateMethodName' => 'BSA.Dialogs.Zend_ProgressBar_Update',
                            'finishMethodName' => 'BSA.Dialogs.Zend_ProgressBar_Finish'));
                $progressBar = new Zend_ProgressBar($adapter, 0, 100);

                for ($i = 1; $i <= 100; $i++) {
                    if ($i < 20) {
                        $text = 'Just beginning';
                    } else if ($i < 50) {
                        $text = 'A bit done';
                    } else if ($i < 80) {
                        $text = 'Getting closer';
                    } else {
                        $text = 'Nearly done';
                    }

                    $progressBar->update($i, $text);
                    usleep(100000);
                }
                $progressBar->finish();

                die;

                break;
            default:
                break;
        }

        // Создать PDF файл из HTML
        $urlFilePDF = Default_Plugin_SysBox::mpdfGenerator_Html2PDF($pdfParams);

        // Установим свой и предыдущий Breadcrumbs
        $Zend_Auth = Zend_Registry::get("Zend_Auth");
        $lastBreadcrumbs = $Zend_Auth->lastBreadcrumbs;
        $this->_breadcrumbs->addStep($lastBreadcrumbs['title'], $this->getUrl(
                        $lastBreadcrumbs['action'], $lastBreadcrumbs['controller'], $lastBreadcrumbs['module']));
        $this->_breadcrumbs->addStep($this->Translate('Отчет в PDF формате'));

        $this->view->urlFilePDF = $urlFilePDF;
    }

    /**
     * Action - profiler
     * evaluation of the performance of the site
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/profiler
     *
     * @return void
     */
    public function profilerAction() {
        $Zend_Auth = Zend_Registry::get("Zend_Auth");
        if ($Zend_Auth->results_profiler) {
            $resultsProfiler = $Zend_Auth->results_profiler;
            $this->view->resultsProfiler = $resultsProfiler;
        } else {
            $this->view->class_message = 'caution';//'warning';
            $message = array(
                '<em>' . $this->Translate("Внимание") . '!</em>',
                $this->Translate("Информация о быстродействии работы сайта отсутствует") . '.',
                $this->Translate("За решением данного вопроса обратитесь к Администратору WEB сайта."),
            );
            $this->view->message = $message;
        }
        $this->_breadcrumbs->addStep($this->Translate('Оценка быстродействия'));
    }

    /**
     * Action - clearhist
     * clear hist of data debugging
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/clearhist
     *
     * @return void
     */
    public function clearhistAction() {

        $json = array();
        //------------------
        $Zend_Auth = Zend_Registry::get("Zend_Auth");
        $Zend_Auth->results_profiler = array();

        if ($this->_isAjaxRequest) {
            $json = array(
                'class_message' => 'information',
                'messages' => array(
                    '<em>' . $this->Translate("Очистить историю profiler") . '!</em>',
                    $this->Translate("История данных profiler - очищена") . '.',
                )
            );
            $this->sendJson($json);
        } else {
            $this->view->class_message = 'information';
            $message = array(
                '<em>' . $this->Translate("Очистить историю profiler") . '!</em>',
                $this->Translate("История данных profiler - очищена") . '.',
            );
            $this->view->message = $message;
        }
        $this->_breadcrumbs->addStep($this->Translate('Очистить историю'));
    }
    
    /**
     * Action - clearcache
     * clear cache
     *
     * Access to the action is possible in the following paths:
     * - /admin/tools/clearcache
     *
     * @return void
     */
    public function clearcacheAction() {

        $json = array();
        //------------------
        $dbCache = Default_Plugin_SysBox::getCache('db');
        $paginatorCache = Default_Plugin_SysBox::getCache('paginator');
        $pageCache = Default_Plugin_SysBox::getCache('page');
        
        if($dbCache){
            $dbCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
        
        if($paginatorCache){
            $paginatorCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
        
        if($pageCache){
            $pageCache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }

        if ($this->_isAjaxRequest) {
            $json = array(
                'class_message' => 'information',
                'messages' => array(
                    '<em>' . $this->Translate("Очистить кеш") . '!</em>',
                    $this->Translate("Кеш очищен") . '.',
                )
            );
            $this->sendJson($json);
        } else {
            $this->view->class_message = 'information';
            $message = array(
                '<em>' . $this->Translate("Очистить кеш") . '!</em>',
                $this->Translate("Кеш очищен") . '.',
            );
            $this->view->message = $message;
        }
        $this->_breadcrumbs->addStep($this->Translate('Очистить кеш'));
    }

}

