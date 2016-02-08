/**
 * Bootstrap - функции инициализации
 *
 * С помощью этих функций вы можете:
 *  - зарегистрировать выполнение любой функцию
 *  после загрузки DOM модели
 *  - определить последовательность выполения ф-ий
 *  - определимть ссылку на обьект класса LangBox (локализация сообщений)
 *  - определимть ссылку на список параметров скриптов
 *  - определимть ссылку на список экземпляров обьектов соответствующих классов
 *
 * JavaScript
 *
 * Copyright (c) 2011 Бескоровайный Сергей
 *
 * @author     Бескоровайный Сергей <bs261257@gmail.com>
 * @copyright  2011 Бескоровайный Сергей
 * @license    BSD
 * @version    1.00.00
 * @link       http://my-site.com/web
 */

// Ссылка на обьект класса LangBox (локализация сообщений)
var lb;
// Список параметров скриптов
var scriptParams = new Hash();
// Список экземпляров обьектов соответствующих классов
var scriptInstances = new Hash();

// Добавить параметр для скриптов
function addScriptParams(myclass, params) {
    if (scriptParams.get(myclass)) {
        scriptParams.get(myclass).push(params);
    } else {
        scriptParams.set(myclass, [params]);
    }
}

/* 
 * runOnLoad.js: переносимый способ регистрации обработчиков события onload.
 *
 * Данный модуль определяет единственную функцию runOnLoad(),
 * выполняющую регистрацию переносимым способом функцийобработчиков,
 * которые могут вызываться только после полной загрузки документа,
 * когда будет доступна стуктура DOM.
 *
 * Функциям, зарегистрированным с помощью runOnLoad(), не передается ни одного
 * аргумента при вызове. Они вызываются не как методы какоголибо объекта
 * и потому в них не должно использоваться ключевое слово this.
 * Функции, зарегистрированные с помощью runOnLoad(), вызываются
 * в порядке их регистрации. При этом нет никакой возможности отменить
 * регистрацию функции после того, как она передана функции runOnLoad().
 *
 * В старых броузерах, не поддерживающих addEventListener() или attachEvent(),
 * эта функция выполняет регистрацию с использованием свойства window.onload
 * модели DOM уровня 0. Она будет работать некорректно в документах,
 * где установлен атрибут onload в тегах <body> или <frameset>.
 */
function runOnLoad(f) {
    if (runOnLoad.loaded) {
        f(); // Если документ уже загружен, просто вызвать f().
    } else {
        runOnLoad.funcs.push(f); // Иначе сохранить для вызова позднее
    }
}
runOnLoad.funcs = []; // Массив функций, которые должны быть вызваны после загрузки документа
runOnLoad.loaded = false; // Функции еще не запускались.
// Запускает все зарегистрированные функции в порядке их регистрации.
// Допускается вызывать runOnLoad.run() более одного раза: повторные
// вызовы игнорируются. Это позволяет вызывать runOnLoad() из функций
// инициализации для регистрации других функций.
runOnLoad.run = function () {
    if (runOnLoad.loaded)
        return; // Если функция уже запускалась, ничего не делать
    for (var i = 0; i < runOnLoad.funcs.length; i++) {
        try {
            runOnLoad.funcs[i]();
        }
        // Исключение, возникшее в одной из функций,
        // не должно делать невозможным запуск оставшихся
        catch (ex) {
            if (ex instanceof Error) { // Это экземпляр Error или подкласса?
                var message = ex.stack;
                if(BSA.Sys && BSA.Sys.messagebox_write){
                    BSA.Sys.messagebox_write('caution', [message]);
                }else{
                    alert(message);
                }
            }
        }
    }
    runOnLoad.loaded = true; // Запомнить факт запуска.
    delete runOnLoad.funcs; // Но не запоминать сами функции.
    delete runOnLoad.run; // И даже забыть о существовании этой функции!
};
// Зарегистрировать метод runOnLoad.run() как обработчик события onload окна
if (window.addEventListener) {
    window.addEventListener("load", runOnLoad.run, false);
} else if (window.attachEvent) {
    window.attachEvent("onload", runOnLoad.run);
} else {
    window.onload = runOnLoad.run;
}
