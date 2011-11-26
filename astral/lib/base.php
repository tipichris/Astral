<?php
/**
* Astral base application file.
*
* $Horde: astral/lib/base.php
* Copyright 2009 The Horde Project (http://www.horde.org/)
*
* See the enclosed file COPYING for license information (LGPL). If you
* did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
*
* @author  Chris Hastie <chris@raggedstaff.net>
* @since   Horde 3.0
* @package Astral
*/



// Check for a prior definition of HORDE_BASE (perhaps by an auto_prepend_file
// definition for site customization).
if (!defined('HORDE_BASE')) {
  @define('HORDE_BASE', dirname(__FILE__) . '/../..');
}

// Load the Horde Framework core, and set up inclusion paths.
require_once HORDE_BASE . '/lib/core.php';

// Registry.
$registry = &Registry::singleton();
if (is_a(($pushed = $registry->pushApp('astral', !defined('AUTH_HANDLER'))), 'PEAR_Error')) {
    if ($pushed->getCode() == 'permission_denied') {
        Horde::authenticationFailureRedirect();
    }
    Horde::fatal($pushed, __FILE__, __LINE__, false);
}
$conf = &$GLOBALS['conf'];
@define('ASTRAL_TEMPLATES', $registry->get('templates'));

// Notification system.
$notification = &Notification::singleton();
$notification->attach('status');

// Define the base file path of Astral.
@define('ASTRAL_BASE', dirname(__FILE__) . '/..');

// Astral base library
require_once ASTRAL_BASE . '/lib/Astral.php';

// Start output compression.
Horde::compressOutput();
?>