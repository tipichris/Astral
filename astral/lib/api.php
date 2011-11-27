<?php
/**
 * Astral external API interface.
 *
 * This file defines Astral's external API interface. Other applications
 * can interact with Astral through this API.
 *
 * $Horde: astral/lib/api.php,
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @package Astral
 * @author Chris Hastie <chris@raggedstaff.net>
 */

$_services['dial'] = array(
    'args' => array('args' => '{urn:horde}hash', 'extra' => '{urn:horde}hash'),
    'type' => 'string'
);

function _astral_dial($args = array(), $extra = array())
{
    global $registry;
    global $conf;
    $number = $args;
    $number = preg_replace("/^\+/", $conf['asteriskmanager']['intlprefix'], $number);
    $number = preg_replace("/[^0-9\*#]/", '', $number);
    $url = Horde::url($registry->get('webroot', 'astral'). '/index.php?ext=' . $number . '&amp;adurl=' . urlencode($_SERVER["SCRIPT_URI"] . '?' . $_SERVER["QUERY_STRING"]));
    return $url;
}