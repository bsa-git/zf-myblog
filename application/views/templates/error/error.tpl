{*
/**
 * error.error
 * 
 * View для отображения ошибок 
 *
 *
 * @package    Module-Default
 * @subpackage Views.Error
 */
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"; "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Application Error</title>
    </head>
    <body>
        <h1 style="color:red">{$message}</h1>


        {if $env == 'development'}

            <h2>Error information:</h2>

            <p><b>Message: </b>{$errMsg}</p>

            <h3>Tracing the query:</h3>

            <TABLE border="0" >
            {foreach from=$arrTraceException item=traceException}
                {if $traceException != ''}
                <TR><TD>{$traceException}</TD></TR>
                {/if}
            {/foreach}
            </TABLE>

            <h3>Request Options:</h3>
            <ul>
                {foreach from=$requestParams key=k item=v}
                    {if $k != 'error_handler'}
                        <li>{$k}: {$v}</li>
                    {/if}    
                {/foreach}
            </ul>
        {else}
            <h2>The site is in maintenance</h2>
            <h3>System error! Please try again later.</h3>
        {/if}

    </body>
</html>
