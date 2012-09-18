<?php

class C2_ResourceServer
{
    private $_hosts;
    private $_protocol;
    private $_port;
    private $_fields;
    private $_timeout;
    
    private $_error;
    
    public function __construct(array $hosts, $protocol = 'ftp', $port = 21, $fields = array(), $timeout = 30)
    {
        $this->_hosts = $hosts;
        $this->_protocol = $protocol;
        $this->_port = $port;
        $this->_fields = $fields;
        $this->_timeout = $timeout;
    }
    
    public function getError()
    {
        return $this->_error;
    }
    
    public function ingest($source, $dest)
    {
        if (empty($this->_hosts)) {
            throw new C2_Exception('Empty hosts.');
        }
        $this->_error = null;
        foreach ($this->_hosts as $host) {
            switch ($this->_protocol) {
                case 'http':
                    $ctx = stream_context_create(array('http' => array('method' => 'POST')));
                    $file = sprintf('http://%s%s', $host, $dest);
                    if (@copy($source, $file, $ctx) === false) {
                        $this->_error[] = array($host, 'Copy failed');
                        continue;
                    }
                    continue;
                case 'ftp':
                    $con = @ftp_connect($host, $this->_port, $this->_timeout);
                    if ($con === false) {
                        $this->_error[] = array($host, 'Connect failed');
                        continue;
                    }
                    $l = ftp_login($con, $this->_fields['user'], $this->_fields['pass']);
                    if ($l === false) {
                        $this->_error[] = array($host, 'Login failed');
                        ftp_close($con);
                        continue;
                    }
                    $r = ftp_put($con, $dest, $source, $this->_fields['mode']);
                    if ($r === false) {
                        $this->_error[] = array($host, 'Put failed');
                    }
                    ftp_close($con);
                    continue;
                case 'scp':
                    $con = ssh2_connect($host, $this->_port, $this->_fields['method'], $this->_fields['callback']);
                    if ($con === false) {
                        $this->_error[] = array($host, 'Connect failed');
                        continue;
                    }
                    // Fingerprint
                    if (!empty($this->_fields['knownhosts'])) {
                        $fingerprint = ssh2_fingerprint($con, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
                        if (!in_array($fingerprint, $this->_fields['knownhosts'])) {
                            $this->_error[] = array($host, 'Fingerprint mismatch');
                            continue;
                        }
                    }
                    // Password authentication.
                    if (!empty($this->_fields['user']) && !empty($this->_fields['pass'])) {
                        $a = ssh2_auth_password($con, $this->_fields['user'], $this->_fields['pass']);
                        if ($a === false) {
                            $this->_error[] = array($host, 'Password authentication failed');
                            continue;
                        }
                    }
                    // Pubkey authentication.
                    if (!empty($this->_fields['user']) && !empty($this->_fields['pubkeyfile']) && !empty($this->_fields['privkeyfile'])) {
                        $p = ssh2_auth_pubkey_file($con, $this->_fields['user'], $this->_fields['pubkeyfile'], $this->_fields['privkeyfile'], $this->_fields['passphrase']);
                        if ($p === false) {
                            $this->_error[] = array($host, 'Pubkey authentication failed');
                            continue;
                        }
                    }
                    $r = ssh2_scp_send($con, $source, $dest, $this->_fields['create_mode']);
                    if ($r === false) {
                        $this->_error[] = array($host, 'SCP send failed');
                        continue;
                    }
                    continue;
                default:
                    throw new C2_Exception('Unknown protocol.');
            }
        }
        if (!empty($this->_error)) {
            return false;
        }
        return true;
    }
}