<?php

/*
*
* @package yariko
*
*/

namespace Es\Inc\Base;

class Deactivate{

    public static function deactivate(){
        flush_rewrite_rules();
    }
}
