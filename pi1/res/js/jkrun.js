/*
 * @author    Dominic Simm
 * @version   0.1
 * @date      06-30-2011
 */

jQuery(function () {
        jQuery.jknav.DEBUG = false;
        // Using default j, k
        jQuery('div.csc-default[id^="c"]').jknav('', 'absatz');
        jQuery.jknav.init({name: 'absatz', speed: 'slow', circular: true, reevaluate: true});

        // Bind up/down using jquery.hotkeys
        jQuery('html').bind('keyup', 'up', function(){jQuery.jknav.up('absatz');});
        jQuery('html').bind('keyup', 'down', function(){jQuery.jknav.down('absatz');});
        });
