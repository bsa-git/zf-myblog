<?php

/**
 * smarty_function_geturl
 *
 * Функция Smarty - type_media
 * Определим тип медиа пр. потоковое или нет?
 *
 * @uses       
 * @package    Module-Default
 * @subpackage Views.Plugins
 */

/**
 * Определить вид медиа из набора обьектов
 * 
 * @param array $medias
 * @param string $type
 * @param int $result
 * @return void
 */
function smarty_function_type_media($params, $smarty) {
    extract($params);// Преобразуем массив параметров в переменные
    $count = 0;
    foreach ($medias as $media) {
        if($type == 'streaming'){
            if($media->type == 'url-rtmp' || $media->type == 'url-pseudostreaming' || $media->type == 'url-httpstreaming'){
                $count ++;
            }
        }
        
        if($type == 'no_streaming'){
            if(! ($media->type == 'url-rtmp' || $media->type == 'url-pseudostreaming' || $media->type == 'url-httpstreaming')){
                $count ++;
            }
        }
    }
    $smarty->assign($assign, $count);
}

?>