<?php
/**
* $Horde: astral/config/prefs.php.dist
*
* See horde/config/prefs.php for documentation on the structure of this file.
*/

$prefGroups['Main'] = array(
    'column' => _("Main Preferences"),
    'label' => _("Main Preferences"),
    'desc' => _("Set your main preferences."),
    'members' => array('channel', 'timeout')
);

$_prefs['channel'] = array(
    'value' => '',
    'locked' => false,
    'shared' => false,
    'type' => 'text',
    'desc' => _("The phone to dial out from (eg 'SIP/myphone')")
);

$_prefs['timeout'] = array(
    'value' => 10,
    'locked' => false,
    'shared' => false,
    'type' => 'number',
    'desc' => _("How long to ring the source phone for (seconds)")
);