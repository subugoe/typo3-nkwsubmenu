/*
 * @author    Dominic Simm
 * @version   0.1
 * @date      06-30-2011
 */

$(function () {
        $.jknav.DEBUG = true;
        // Using default j, k
        $('div.csc-default[id^="c"]').jknav('', 'absatz');
        $.jknav.init({name: 'absatz', speed: 'slow', circular: true, reevaluate: true});

        // Bind up/down using jquery.hotkeys
        $('html').bind('keyup', 'up', function(){$.jknav.up('absatz');});
        $('html').bind('keyup', 'down', function(){$.jknav.down('absatz');});
        });
