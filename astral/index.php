<?php
/**
 * $Horde: astral/index.php
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author Chris Hastie <chris@raggedstaff.net>
 * @package Astral
 */

@define('ASTRAL_BASE', dirname(__FILE__));
$astral_configured = (is_readable(ASTRAL_BASE . '/config/conf.php') &&
                        is_readable(ASTRAL_BASE . '/config/prefs.php'));


if (!$astral_configured) {
    require ASTRAL_BASE . '/../lib/Test.php';
    Horde_Test::configFilesMissing('Astral', ASTRAL_BASE,
                                   array('conf.php', 'prefs.php'));
}


require_once ASTRAL_BASE . '/lib/base.php';
require_once ASTRAL_BASE . '/lib/AsteriskManager.php';

$channel = $prefs->getValue('channel');
$timeout = $prefs->getValue('timeout') >= 5 ? 1000*$prefs->getValue('timeout') : 5000;

$url = isset($_REQUEST['adurl'])? $_REQUEST['adurl'] : $registry->get('webroot', 'turba');

$result = '';
if (empty($channel)) {
    $notification->push(_("No source phone configured for the call"), 'horde.error');
    $notification->push(_("You can configure a phone in the Options for Asterisk Dialer"), 'horde.message');
    header('Location: ' . $url);
    exit;
}


else {
    $astman = new AsteriskManager;

    $loggedin = $astman->login($conf['asteriskmanager']['host'], $conf['asteriskmanager']['username'], $conf['asteriskmanager']['secret']);

    if (empty($loggedin)) {
        $notification->push( _("Login to Asterisk manager failed: ") . $astman->getError(), 'horde.error');
        header('Location: ' . $url);
        exit;
    }
    else {
        $astman->setContext($conf['asteriskmanager']['context']);
        $astman->setPriority($conf['asteriskmanager']['priority']);
        $result .= $astman->call($channel, $_REQUEST['ext'], $timeout);

        $loggedout = $astman->logout();
    }
}

if (empty($result)) {
    $notification->push(_("Call origination failed"), 'horde.error');
} else {
    $notification->push(_("Dialing succeeded"), 'horde.success');
}

$messages = $astman->getMessage();
foreach ($messages as $msg) {
    $notification->push($msg, 'horde.message');
}

if (!empty($url)){
    header('Location: ' . $url);
    exit;
}

// we should probably never get here

$title = _("Calling...");

require ASTRAL_TEMPLATES . '/common-header.inc';
require ASTRAL_TEMPLATES . '/menu.inc';
echo "<pre>\n" . $result . "\n</pre>";
require $registry->get('templates', 'horde') . '/common-footer.inc';