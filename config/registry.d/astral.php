<?php

$this->applications['astral'] = array(
    'fileroot' => dirname(__FILE__) . '/../../astral',
    'webroot' => $this->applications['horde']['webroot'] . '/astral',
    'name' => _("Asterisk dialer"),
    'status' => 'notoolbar',
    'provides' => array('telephony/dial'),
);

?>