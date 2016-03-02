/**
 * Bootstrap - initialization functions
 *
 * With these functions you can:
 *  - register perform any function after loading the DOM
 *  - determine the sequence of execution functions
 *  - define a reference to the class object LangBox (localization of messages)
 *  - define a link to a list of script parameters
 *  - define a link to a list of instances of objects of the classes
 *
 * JavaScript
 *
 * @author   Sergii Beskorovainyi <bsa2657@yandex.ru>
 * @license  MIT <http://www.opensource.org/licenses/mit-license.php>
 * @link     https://github.com/bsa-git/zf-myblog/
 */

// Reference to the class object LangBox (localization of messages)
var lb;
// List script parameters
var scriptParams = new Hash();
// List of instances of objects of the classes
var scriptInstances = new Hash();

// Add parameters for scripts
function addScriptParams(myclass, params) {
    if (scriptParams.get(myclass)) {
        scriptParams.get(myclass).push(params);
    } else {
        scriptParams.set(myclass, [params]);
    }
}

/* 
 * runOnLoad.js: method of registering an onload event handler.
 *
 * This module defines a single function runOnLoad(), 
 * performs registration functions? 
 * Handlers that can be called only after a full load a DOM document.
 *
 */
function runOnLoad(f) {
    if (runOnLoad.loaded) {
        f(); // If the document is already loaded, cause simply f().
    } else {
        runOnLoad.funcs.push(f); // Otherwise, save to call later
    }
}
runOnLoad.funcs = []; // An array of functions that should be called after the document is loaded
runOnLoad.loaded = false; // Functions not yet launched.
// Launches all registered functions in order of their registration.
runOnLoad.run = function () {
    if (runOnLoad.loaded)
        return; // If the function is already run, do nothing
    for (var i = 0; i < runOnLoad.funcs.length; i++) {
        try {
            runOnLoad.funcs[i]();
        }
        // The exception has arisen in one of the functions that should not make it impossible to run the remaining
        catch (ex) {
            if (ex instanceof Error) { // This is an instance or subclass of Error?
                var message = ex.stack;
                if (BSA.Sys && BSA.Sys.messagebox_write) {
                    BSA.Sys.messagebox_write('caution', [message]);
                } else {
                    alert(message);
                }
            }
        }
    }
    runOnLoad.loaded = true; // Remember fact launch.
    delete runOnLoad.funcs; // But do not remember the functions themselves.
    delete runOnLoad.run; // And even forgotten the existence of the function!
};
// Register method run OnLoad.run () as the onload event handler of the window
if (window.addEventListener) {
    window.addEventListener("load", runOnLoad.run, false);
} else if (window.attachEvent) {
    window.attachEvent("onload", runOnLoad.run);
} else {
    window.onload = runOnLoad.run;
}
