<?php
/**
 * The AsteriskManager:: class contains functions for communicating
 * with the Asterisk manager interface
 *
 * $Horde: astral/lib/AsteriskManager.php,
 *
 * Copyright 2007-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * Partly based on a Public Domain class found here:
 * http://www.voip-info.org/wiki/view/Asterisk+manager+Example%3A+PHP
 *
 * @author pnsystem at comhem.se
 * @author Chris Hastie <chris@raggedstaff.net>
 * @package Astral
 *
 */

class AsteriskManager {

    /**
     * The socket to Asterisk manager
     *
     * @var reference
     */
    var $socket;

    /**
     * Latest error message
     *
     * @var string
     */
    var $error;

    /**
     * Messages returned by the Asterisk manager
     *
     * @var array
     */
     var $message = array();

    /**
     * Constructor.
     */
    function AsteriskManager()
    {
        $this->socket = FALSE;
        $this->error = "";
        $this->context = 'internal';
        $this->priority = '1';
    }

    /**
     * Login to Asterisk manager
     *
     * @param string $host      Host to connect to (optionally with :port appended)
     * @param string $username  Username to login with
     * @param string $password  Password
     *
     * @return boolean
     *
     */
    function login($host='localhost', $username, $password)
    {
        if (preg_match("/(.*):(\d*)/", $host, $matches)) {
          $host = $matches[1];
          $port = $matches[2];
        } else {
          $port = "5038";
        }

        $this->socket = @fsockopen($host, $port, $errno, $errstr, 1);
        if (!$this->socket) {
            $this->error =  "Could not connect to $host:$port - $errstr ($errno)";
            return FALSE;
        } else {
            stream_set_timeout($this->socket, 1);
            $wrets = $this->_query("Action: Login\r\nUserName: $username\r\nSecret: $password\r\nEvents: off\r\n\r\n");
            // if (strpos($wrets, "Message: Authentication accepted") != FALSE){
            if (preg_match("/response:\s*success/im", $wrets)) {
                return true;
            } else {
                $this->error = "Could not login - Authentication failed";
                fclose($this->socket);
                $this->socket = FALSE;
                return FALSE;
            }
        }
    }

    /**
     * Logout of Asterisk Manager
     */
    function logout()
    {
        if ($this->socket){
            fputs($this->socket, "Action: Logoff\r\n\r\n");
            while (!feof($this->socket)) {
                $wrets .= fread($this->socket, 8192);
            }
            fclose($this->socket);
            $this->socket = "FALSE";
        }
        return;
    }

    /**
     * Send a query to Asterisk manager
     *
     * @access private
     *
     * @param string $query  The query to send
     *
     * @return string  The response
     *
     */
    function _query($query){
        $wrets = "";

        if ($this->socket === FALSE) {
            return FALSE;
        }

        fputs($this->socket, $query);
        do {
            $line = fgets($this->socket, 4096);
            $wrets .= $line;
        } while ($line != "\r\n" );

        return $wrets;
    }

    /**
     * Fetch the last error message
     *
     * @return string  The last error message
     *
     */
    function getError()
    {
        return $this->error;
    }

    /**
     * Fetch any messages returned by the Asterisk manager
     *
     * @return array  Array of messages
     *
     */
    function getMessage()
    {
        return $this->message;
    }

    /**
     * Retrieve a database entry
     *
     * @param string $family  The database family
     * @param string $key     The database key
     *
     * @return string  The value retrieved
     *
     */
    function getDB($family, $key)
    {
        $value = "";

        $wrets = $this->_query("Action: Command\r\nCommand: database get $family $key\r\n\r\n");

        if ($wrets) {
            $value_start = strpos($wrets, "Value: ") + 7;
            $value_stop = strpos($wrets, "\n", $value_start);
            if ($value_start > 8) {
                  $value = substr($wrets, $value_start, $value_stop - $value_start);
            }
        }
        return $value;
    }

    /**
     * Add a database entry
     *
     * @param string $family  The database family
     * @param string $key     The database key
     * @param string $value   The value to add
     *
     * @return boolean
     *
     */
    function putDB($family, $key, $value)
    {
        $wrets = $this->_query("Action: Command\r\nCommand: database put $family $key $value\r\n\r\n");

        if (strpos($wrets, "Updated database successfully") != FALSE){
            return TRUE;
        }
        $this->error =  "Could not updated database";
        return FALSE;
    }

     /**
     * Delete a database entry
     *
     * @param string $family  The database family
     * @param string $key     The database key
     *
     * @return boolean
     *
     */
    function delDB($family, $key)
    {
        $wrets = $this->_query("Action: Command\r\nCommand: database del $family $key\r\n\r\n");

        if (strpos($wrets, "Database entry removed.") != FALSE){
            return TRUE;
        }
        $this->error =  "Database entry does not exist";
        return FALSE;
    }

    /**
     * Retrieve an entire database family
     *
     * @param string $family  The database family
     *
     * @return array  array of key => value pairs, or false on failure
     *
     */
    function getFamilyDB($family)
    {
        $wrets = $this->_query("Action: Command\r\nCommand: database show $family\r\n\r\n");
        if ($wrets) {
            $value_start = strpos($wrets, "Response: Follows\r\n") + 19;
            $value_stop = strpos($wrets, "--END COMMAND--\r\n", $value_start);
            if ($value_start > 18) {
                $wrets = substr($wrets, $value_start, $value_stop - $value_start);
            }
            $lines = explode("\n", $wrets);
            foreach($lines as $line) {
                if (preg_match("/Privilege/i", $line)) {
                    continue;
                }
                if (strlen($line) > 4) {
                    $value_start = strpos($line, ": ") + 2;
                    $value_stop = strpos($line, " ", $value_start);
                    $key = trim(substr($line, strlen($family) + 2, strpos($line, " ") - strlen($family) + 2));
                    $value[$key] = trim(substr($line, $value_start));
                }
            }
            return $value;
        }
        return FALSE;
    }

    /**
     * Set the context in which calls are placed
     */
    function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Set the initial priority for calls
     */
    function setPriority($priority) {
        $this->priority = $priority;
    }


    /**
     * Place a call
     *
     * @param string $channel    The channel to originate the call from
     * @param string $extension  The extension (number) to dial
     * @param string $timeout    Timout in ms to call $channel for
     *
     * @return boolean
     *
     */
    function call($channel, $extension,$timeout=10000)
    {
        $wrets = $this->_query("Action: Originate\r\nChannel: $channel\r\nExten: $extension\r\nContext: " .$this->context . "\r\nPriority: " .$this->priority . "\r\nTimeout: $timeout\r\n\r\n");
        return $this->_procResponse($wrets);
    }

    /**
     * Process a response from Asterisk manager
     *
     * @access private
     *
     * @param string $resp  The response to process
     *
     * @return boolean
     *
     */
    function _procResponse($resp)
    {
        $lines = explode("\r\n", $resp);
        $status = 0;
        $this->message = '';
        foreach ($lines as $line) {
            trim($line);
            if (preg_match("/response:\s*(.*)/i", $line, $matches)) {
                if (preg_match("/Success/i", $matches[1])) {
                $status = 1;
                }
            }
            elseif (!empty($line)) {
                $this->message[] = $line;
            }
        }
        return $status;
    }
}
?>