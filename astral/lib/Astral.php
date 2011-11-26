<?php
/**
* Astral Base Class.
*
* $Horde: astral/lib/Astral.php
*
* Copyright 2007-2009 The Horde Project (http://www.horde.org/)
*
* See the enclosed file COPYING for license information (GPL). If you
* did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
*
* @author Chris Hastie <chris@raggedstaff.net>
* @package Astral
*/

class Astral {

    /**
    * Build Astral's list of menu items.
    */
    function getMenu($returnType = 'object')
    {
        global $conf, $registry, $browser, $print_link;

        require_once 'Horde/Menu.php';

        $menu = new Menu(HORDE_MENU_MASK_ALL);

        if ($returnType == 'object') {
            return $menu;
        } else {
            return $menu->render();
        }
    }
}