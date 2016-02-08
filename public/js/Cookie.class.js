/**
 * Cookie - Class
 *
 * Класс для работы с Cookie
 *
 * JavaScript
 *
 *
 * @author     Дэвид Флэнаган
 */
   
       
/**
* Это функция-конструктор Cookie().
*
* Данный конструктор отыскивает cookie с заданным именем для текущего документа.
* Если cookie существует, его значение интерпретируется как набор пар имя–значение,
* после чего эти значения сохраняются в виде свойств вновь созданного объекта.
*
* Чтобы сохранить в cookie новые данные, достаточно просто установить
* значение свойства объекта Cookie. Избегайте использования свойств
* с именами "store" и "remove", поскольку эти имена зарезервированы для методов объекта.
* Чтобы сохранить данные cookie в локальном хранилище веб-броузера,
* следует вызвать метод store().
* Чтобы удалить данные cookie из хранилища броузера, нужно вызвать метод remove().
*
* Статический метод Cookie.enabled() возвращает значение true,
* если использование cookies разрешено в броузере, в противном случае
* возвращается значение false.
*/
function Cookie(name)
{
    this.$name = name; // Запомнить имя данного cookie
    // Прежде всего необходимо получить список всех cookies,
    // принадлежащих этому документу.
    // Для этого следует прочесть содержимое свойства Document.cookie.
    // Если ни одного cookie не найдено, ничего не предпринимать.
    var allcookies = document.cookie;
    if (allcookies == "") return;
    // Разбить строку на отдельные cookies, а затем выполнить
    // цикл по всем полученным строкам в поисках требуемого имени.
    var cookies = allcookies.split(';');
    var cookie = null;
    for(var i = 0; i < cookies.length; i++) {
        // Начинается ли текущая строка cookie с искомого имени?
        if (cookies[i].substring(0, name.length+1) == (name + "=")) {
            cookie = cookies[i];
            break;
        }
    }
    // Если cookie с требуемым именем не найден, вернуть управление
    if (cookie == null) return;
    // Значение cookie находится вслед за знаком равенства
    var cookieval = cookie.substring(name.length+1);
    // После того как значение именованного cookie файла получено,
    // необходимо разбить его на отдельные пары имя–значение переменных
    // состояния. Пары имя–значение отделяются друг от друга символом
    // амперсанда, а имя от значения внутри пары отделяется двоеточиями.
    // Для интерпретации значения cookie файла используется метод split().
    var a = cookieval.split('&'); // Превратить в массив пар имя–значение
    for(var i=0; i < a.length; i++) // Разбить каждую пару в массиве
        a[i] = a[i].split(':');
    // Теперь, после того как закончена интерпретация значения cookie,
    // необходимо определить свойства объекта Cookie и установить их значения.
    // Обратите внимание: значения свойств необходимо декодировать,
    // потому что метод store() кодирует их.
    for(var i = 0; i < a.length; i++) {
        //        if(a[i][1] === undefined){
        //            this[this.$name] = decodeURIComponent(a[i][0]);
        //        }else{
        this[a[i][0]] = decodeURIComponent(a[i][1]);
    //        }
        
    }
        
}
/**
* Данная функция - метод store() объекта Cookie.
*
* Аргументы:
*
* daysToLive: срок жизни cookie-файла в сутках. Если установить значение
* этого аргумента равным нулю, cookie будет удален. Если установить
* значение null или опустить этот аргумент, срок жизни cookie будет
* ограничен продолжительностью сеанса и сам cookie не будет сохранен
* броузером по завершении работы. Этот аргумент используется
* для установки значения атрибута max-age в cookie-файле.
* path: значение атрибута path в cookie
* domain: значение атрибута domain в cookie
* secure: если передается значение true, устанавливается
* атрибут secure в cookie-файле
*/
Cookie.prototype.store = function(daysToLive, path, domain, secure){
        
    // Сначала нужно обойти в цикле свойства объекта Cookie и объединить
    // их в виде значения cookie-файла. Поскольку символы знака равенства
    // и точки с запятой используются для нужд оформления cookies,
    // для отделения друг от друга переменных состояния, составляющих
    // значение cookie-файла, используются символы амперсанда,
    // а для отделения имен и значений внутри пар - двоеточия.
    // Обратите внимание: значение каждого свойства необходимо
    // кодировать на случай, если в них присутствуют знаки пунктуации
    // или другие недопустимые символы.
    var cookieval = "";
        
    for(var prop in this) {
        // Игнорировать методы, а также имена свойств, начинающиеся с '$'
        if ((prop.charAt(0) == '$') || ((typeof this[prop]) == 'function'))
            continue;
        if (cookieval != "") cookieval += '&';
        if(this[prop] === 'undefined'){
            cookieval += prop;
        }else{
            cookieval += prop + ':' + encodeURIComponent(this[prop]);
        }
    //        cookieval += prop + ':' + encodeURIComponent(this[prop]);
    }
        
    // Теперь, когда получено значение cookie-файла, можно создать полную
    // строку cookie-файла, которая включает имя и различные атрибуты,
    // заданные при создании объекта Cookie
    var cookie = this.$name + '=' + cookieval;
        
    if (daysToLive || daysToLive == 0) {
        cookie += "; max-age=" + (daysToLive*24*60*60);
    }
    if (path) cookie += "; path=" + path;
    if (domain) cookie += "; domain=" + domain;
    if (secure) cookie += "; secure";
    // Теперь нужно сохранить cookie, установив свойство Document.cookie
    document.cookie = cookie;
}
    
/**
* Эта функция - метод remove() объекта Cookie; он удаляет свойства объекта
* и сам cookie из локального хранилища броузера.
*
* Все аргументы этой функции являются необязательными, но чтобы удалить
* cookie, необходимо передать те же значения, которые передавались методу store().
*/
Cookie.prototype.remove = function(path, domain, secure){
    // Удалить свойства объекта Cookie
    for(var prop in this) {
        if (prop.charAt(0) != '$' && typeof this[prop] != 'function')
            delete this[prop];
    }
    // Затем сохранить cookie со сроком жизни, равным 0
    this.store(0, path, domain, secure);
}

/**
* Эта функция - метод getValue() объекта Cookie; 
* с помощью этого метода мы получаем значение по ключу для Cookie
* если это одиночное значение, (пр. 'PHPSESSID=hkjkjahfhghgweqwuhhhghgjh'), 
* то значение равно самому свойству которое равно - 'undefined'
* при этом ключ - key должен быть равен -> this.$name
*
*/
Cookie.prototype.getValue = function(key){
    // Удалить свойства объекта Cookie
    for(var prop in this) {
        if (prop.charAt(0) != '$' && typeof this[prop] != 'function'){
            if(prop === key){
                return this[prop];
            }
            if(key == this.$name && this[prop] == 'undefined'){
                return prop;
            }
        }
    }
    return null;
}


/**
* Эта функция - метод setValue() объекта Cookie; 
* с помощью этого метода мы устанавливаем значение по ключу для Cookie
* если это одиночное значение, (пр. 'PHPSESSID=hkjkjahfhghgweqwuhhhghgjh'), 
* то значение становиться свойством обьекта, а его значение -> 'undefined'
*
*/
Cookie.prototype.setValue = function(key,value){
    // Удалить свойства объекта Cookie
    for(var prop in this) {
        if (prop.charAt(0) != '$' && typeof this[prop] != 'function'){
            if(key == this.$name && this[prop] == 'undefined'){
                delete this[prop];
                if(value === null && value === undefined){
                }else{
                    this[value] = 'undefined';
                }
                return this;
            }
            
            if(prop === key){
                if(value === null && value === undefined){
                    delete this[prop];
                }else{
                    this[prop] = value;
                }
                return this;
            }
        }
    }
    if(key == this.$name){
        if(value === null && value === undefined){
        }else{
            this[value] = 'undefined';
        }
        return this;
    }
    if(value === null && value === undefined){
    }else{
        this[key] = value;
    }
    return this;
}

/**
* Эта функция - метод getValues() объекта Cookie; 
* с помощью этого метода мы получаем обьект ключей значений,
* где ключи это свойсва обьекта. Кроме случая одиночного значения.
* Здесь ключь равен -> this.$name
*
*/
Cookie.prototype.getValues = function(){
    var values = {};
    // Удалить свойства объекта Cookie
    for(var prop in this) {
        if (prop.charAt(0) != '$' && typeof this[prop] != 'function'){
            if(this[prop] == 'undefined'){
                values[this.$name] = prop;
            }else{
                values[prop] = this[prop];
            }
        }
    }
    return values;
}
    
/**
* Этот статический метод пытается определить, разрешено ли использование cookies
* в броузере. Возвращает значение true, если разрешено, и false - в противном случае.
* Возвращаемое значение true не гарантирует, что сохранение cookies
* фактически разрешено. Временные cookies сеанса по прежнему могут быть
* доступны, даже если этот метод возвращает значение false.
*/
Cookie.enabled = function() {
    // Воспользоваться свойством navigator.cookieEnabled, если оно определено в броузере
    if (navigator.cookieEnabled != undefined) return navigator.cookieEnabled;
    // Если значение уже было помещено в кэш, использовать это значение
    if (Cookie.enabled.cache != undefined) return Cookie.enabled.cache;
    // Иначе создать тестовый cookie с некоторым временем жизни
    document.cookie = "testcookie=test; max-age=10000"; // Установить cookie
    // Теперь проверить - был ли сохранен cookie-файл
    var cookies = document.cookie;
    if (cookies.indexOf("testcookie=test") == -1) {
        // Cookie не был сохранен
        return Cookie.enabled.cache = false;
    }
    else {
        // Cookie был сохранен, поэтому его нужно удалить перед выходом
        document.cookie = "testcookie=test; max-age=0"; // Удалить cookie
        return Cookie.enabled.cache = true;
    }
}
