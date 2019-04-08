<?php
/**
 * Created by PhpStorm.
 * User: jeanbaptistecaplan
 * Date: 09/02/2019
 * Time: 23:58
 */

namespace Plexus\Utils;


class History {

    static $HISTORY_SESSION_IDENTIFIER = "@History.urls";

    /**
     * @description Push the current URL to a session stored array
     */
    static public function pushCurrentURL() {
        // Create the array
        if (!isset($_SESSION[History::$HISTORY_SESSION_IDENTIFIER])) {
            $_SESSION[History::$HISTORY_SESSION_IDENTIFIER] = [];
        }

        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        if (History::getLastURL() != $current_url) {
            // This array keeps the 10 last values
            if (count($_SESSION[History::$HISTORY_SESSION_IDENTIFIER]) > 9) {
                $_SESSION[History::$HISTORY_SESSION_IDENTIFIER] = array_slice($_SESSION[History::$HISTORY_SESSION_IDENTIFIER], -9);
            }
            // Add the current url
            $_SESSION[History::$HISTORY_SESSION_IDENTIFIER][] = $current_url;
        }
    }

    /**
     * @description Returns the last url stored in the session history
     * @return string
     */
    static public function getLastURL() {
        if (!isset($_SESSION[History::$HISTORY_SESSION_IDENTIFIER])) {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        }
        return $_SESSION[History::$HISTORY_SESSION_IDENTIFIER][count($_SESSION[History::$HISTORY_SESSION_IDENTIFIER])-1];
    }
}