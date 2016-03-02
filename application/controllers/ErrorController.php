<?php

/**
 * ErrorController
 *
 * Controller - Error
 * it handles system errors
 *
 * @uses       Default_Plugin_BaseController
 * @package    Module-Default
 * @subpackage Controllers
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */
class ErrorController extends Default_Plugin_BaseController {

    /**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger = null;

    //------------------------------------------

    /**
     * Initialization controller
     *
     * @return void
     */
    public function init() {

        parent::init();

        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->initView();
    }

    /**
     * errorAction() is the action that will be called by the "ErrorHandler"
     * plugin.  When an error/exception has been encountered
     * in a ZF MVC application (assuming the ErrorHandler has not been disabled
     * in your bootstrap) - the Errorhandler will set the next dispatchable
     * action to come here.  This is the "default" module, "error" controller,
     * specifically, the "error" action.  These options are configurable, see
     * {@link http://framework.zend.com/manual/en/zend.controller.plugins.html#zend.controller.plugins.standar
     *
     * @return void
     */
    public function errorAction() {

        $errors = $this->_getParam('error_handler');

        // Get APPLICATION_ENV - (production, testing, development)
        $this->view->env = APPLICATION_ENV;

        if (!$errors) {
            $this->view->message = 'You have reached the error page';
            return;
        } else {
            $message = $errors->exception->getMessage() . "\n\n";
            $message .= $errors->exception->getTraceAsString() . "\n\n";
            $expression = var_export($errors->request->getParams(), true);
            $message .= $expression . "\n\n";
            $uri = $errors->request->getRequestUri();
        }
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);

                // Запомним ошибку в лог файле - Messages.log
                $this->_logMsg->err('Error 404: Resource Not Found ' . $uri);
                // Запомним ошибку в лог файле - Exceptions.log
                $this->_logEx->err($message);
                // Отобразим пользователю
                $this->view->message = 'Resource not found. Please contact your system administrator.';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                // Запомним ошибку в лог файле - Messages.log
                // если разрешено передача ошибки по почте, то
                // она будет передана по почте администратору
                $this->_logMsg->crit('Error 500: Application error ' . $uri);
                // Запомним ошибку в лог файле - Exceptions.log
                $this->_logEx->crit($message);
                // Отобразим пользователю
                $this->view->message = 'Application error. Please contact your system administrator.';
                break;
        }
        
        if ($this->getInvokeArg('displayExceptions') == true) {

            //Получим параметры запроса
            $this->view->requestParams = $this->_params;//$request->getParams();

            //Получим сообщение об ошибке
            $exception = $errors->exception;
            $this->view->errMsg = $exception->getMessage();

            //Получим трассу выполнения скрипта
            $traceException = $exception->getTraceAsString();
            $this->view->arrTraceException = explode('#', $traceException);
        }

        //$this->view->request = $errors->request;
        //Добавим путь к действию
        $this->_breadcrumbs->addStep('Ошибка');
    }

    /**
     * Action - message
     * message display
     *
     * @return void
     */
    public function messageAction() {
        $params = $this->_getAllParams();
        if (isset($params['message'])) {
            $this->view->class_message = $params['class_message'];
            $this->view->message = $params['message'];
        }
        if ($this->_flashMessenger->hasMessages()) {
            $this->view->class_message = 'warning';
            $this->view->message = $this->_flashMessenger->getMessages();
        }

        //Добавим путь к действию
        $this->_breadcrumbs->addStep('Ошибка');
    }

}

