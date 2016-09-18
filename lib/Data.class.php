<?php
/**
 *
 */
class Data {
    protected static $actions;
    protected static $sites;
    
    public static function __callStatic($name, $args) {
        list($var, $type, $lenght) = $args;
        if (!$type) $type = "s";
        if (!$lenght) $lenght = 30;
        switch ($name) {
            case 'post':
                switch ($type) {
                    case 'string': case 's':
                        return is_string($_POST[$var]) && strlen($_POST[$var]) <= $lenght ? $_POST[$var] : false;
                    case 'number': case 'int': case 'integer': case 'i':
                        return is_numeric($_POST[$var]) && $_POST[$var] <= pow(10, $lenght) ? $_POST[$var] : false;
                    default:
                        return false;
                }
                break;
            case 'get':
                switch ($type) {
                    case 'string': case 's':
                        return is_string($_GET[$var]) && strlen($_GET[$var]) <= $lenght ? $_GET[$var] : false;
                    case 'number': case 'int': case 'integer': case 'i':
                        return is_numeric($_GET[$var]) && $_GET[$var] <= pow(10, $lenght) ? $_GET[$var] : false;
                    default:
                        return false;
                }
                break;
            case 'file': case 'files':
                $mime = explode("/", $_FILES[$var]['type']);
                $type = explode("/", $type);
                $typecheck = ($type[0] == "*" || $mime[0] == $type[0]) && ($type[1] == "*" || $mime[1] == $type[1]);
                return $typecheck && $_FILES[$var]['size'] <= $lenght ? $_FILES[$var] : false;
                break;
            case 'server':
                return $_SERVER[$var];
                break;
            case 'action': case 'regAction':
                list($key, $action) = $args;
                if ($action instanceof Action) {
                    Data::$actions[$key] = $action;
                    return true;
                } else {
                    return false;
                }
                break;
            case 'act': case 'preformAction':
                list($key) = $args;
                if (Data::$actions){
                    Data::$actions[$key]->preform();
                    return true;
                } else {
                    return false;
                }
                break;
            case 'site': case 'regSite':
                list($key, $action) = $args;
                if ($action instanceof Action) {
                    Data::$sites[$key] = $action;
                    return true;
                } else {
                    return false;
                }
                break;
            case 'getSite':
                list($key) = $args;
                return Data::$sites[$key];
                break;
            default:
                return false;
        }
    }
    
}