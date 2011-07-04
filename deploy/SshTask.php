<?php
/*
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/Task.php';

/**
 * Execute commands on a remote host using ssh.
 *
 * @author    Johan Van den Brande <johan@vandenbrande.com>
 * @author    Jesse Greathouse <jesse.greathouse@gmail.com>
 * @package   phing.tasks.ext
 */
class SshTask extends Task {

    private $host = "";
    private $port = 22;
    private $username = "";
    private $password = "";
    private $command = "";
    private $pubkeyfile = '';
    private $privkeyfile = '';
    private $sshlib;
    private $privkeyfilepassphrase = '';
    private $crypt = 'RSA';
    
    /**
     * The name of the property to capture (any) output of the command
     * @var string
     */
    private $property = "";
    
    /**
     * Whether to display the output of the command
     * @var boolean
     */
    private $display = true;

    public function setHost($host) 
    {
        $this->host = $host;
    }

    public function getHost() 
    {
        return $this->host;
    }

    public function setPort($port) 
    {
        $this->port = $port;
    }

    public function getPort() 
    {
        return $this->port;
    }

    public function setUsername($username) 
    {
        $this->username = $username;
    }

    public function getUsername() 
    {
        return $this->username;
    }

    public function setPassword($password) 
    {
        $this->password = $password;
    }

    public function getPassword() 
    {
        return $this->password;
    }

    /**
     * Sets the public key file of the user to scp
     */
    public function setPubkeyfile($pubkeyfile)
    {
        $this->pubkeyfile = $pubkeyfile;
    }

    /**
     * Returns the pubkeyfile
     */
    public function getPubkeyfile()
    {
        return $this->pubkeyfile;
    }
    
    /**
     * Sets the private key file of the user to scp
     */
    public function setPrivkeyfile($privkeyfile)
    {
        $this->privkeyfile = $privkeyfile;
    }

    /**
     * Returns the private keyfile
     */
    public function getPrivkeyfile()
    {
        return $this->privkeyfile;
    }
    
    /**
     * Sets the private key file passphrase of the user to scp
     */
    public function setPrivkeyfilepassphrase($privkeyfilepassphrase)
    {
        $this->privkeyfilepassphrase = $privkeyfilepassphrase;
    }

    /**
     * Returns the private keyfile passphrase
     */
    public function getPrivkeyfilepassphrase()
    {
        return $this->privkeyfilepassphrase;
    }

    /**
     * Sets the ssh library identifier
     */
    public function setSshlib($sshlib) 
    {
        $this->sshlib = strtolower($sshlib);
    }

    /**
     * Returns the ssh library identifier
     */
    public function getSshlib() 
    {
        return $this->sshlib;
    }
    
    /**
     * Sets the encryption library identifier
     */
    public function setCrypt($crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Returns the encryption library identifier
     */
    public function getCrypt()
    {
        return $this->crypt;
    }
    
    public function setCommand($command) 
    {
        $this->command = $command;
    }

    public function getCommand() 
    {
        return $this->command;
    }
    
    /**
     * Sets the name of the property to capture (any) output of the command
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }
    
    /**
     * Sets whether to display the output of the command
     * @param boolean $display
     */
    public function setDisplay($display)
    {
        $this->display = Boolean::cast($display);
    }

    public function init() 
    {
    }
    
    public function main()
    {
        switch ($this->sshlib) {

            //if the sshlib attribute is netssh use the netssh method
            case 'netssh':
                if (!$this->requireable('Net/SSH2.php')) { 
                    throw new BuildException("To use SshTask, you must have the Net_SSH2 library (phpseclib.sourceforge.net) in your php include_path.");
                } else {
                    $this->netssh();
                }
            break;
            
            //if the sshlib attribute is ssh2 use the ssh2 method
            case 'ssh2':
                if (!function_exists('ssh2_connect')) { 
                    throw new BuildException("To use SshTask, you must have the PHP ssh2 extension.");
                } else {
                    $this->ssh2();
                }
            break;
            
            //if no sshlib is specified try to find a usable library
            default:
                //first try ssh2 by checking if the function ssh2_connect exists
                if (function_exists('ssh2_connect')) {
                    $this->ssh2();
                } else {
                    //if the first check failed, see if the Net_SSH2 library can be required
                    if (!$this->requireable('Net/SSH2.php')) {
                        throw new BuildException('To use SshTask, you must have the PHP ssh2 extension or the Net_SSH2 library (phpseclib.sourceforge.net) in your php include_path.');
                    } else {
                        $this->netssh();
                    }
                }
            break;
        }
    }

    public function ssh2() 
    {   
        if ($this->host == "" || $this->username == "") {
            throw new BuildException("Attribute 'hostname' and 'username' must be set");
        }
        
        $this->connection = ssh2_connect($this->host, $this->port);
        if (is_null($this->connection)) {
            throw new BuildException("Could not establish connection to " . $this->host . ":" . $this->port . "!");
        }

        $could_auth = null;
        if ( $this->pubkeyfile ) {
            $pubfile = realpath($this->pubkeyfile);
            $privfile = realpath($this->privkeyfile);
            echo $pubfile."\n";
            echo $privfile."\n";
            echo $this->privkeyfilepassphrase."\n";
            $could_auth = ssh2_auth_pubkey_file($this->connection, $this->username, $pubfile, $privfile, $this->privkeyfilepassphrase);
        } else {
            $could_auth = ssh2_auth_password($this->connection, $this->username, $this->password);
        }
        if (!$could_auth) {
            throw new BuildException("Could not authenticate connection!");
        }

        $stream = ssh2_exec($this->connection, $this->command);
        if (!$stream) {
            throw new BuildException("Could not execute command!");
        }
        
        $this->log("Executing command {$this->command}", Project::MSG_VERBOSE);
        
        $output = "";
        stream_set_blocking( $stream, true );
        
        while( $buf = fread($stream,4096) ){
            if ($this->display) {
                print($buf);
            }
            
            $output .= $buf;
        }
        $this->log("Result: {$output}", Project::MSG_VERBOSE);
        if (!empty($this->property)) {
            $this->project->setProperty($this->property, $output);
        }
        
        fclose($stream);
    }
    
    public function netssh() 
    {   
        require_once('Net/SSH2.php');
        if ($this->host == "" || $this->username == "") {
            throw new BuildException("Attribute 'hostname' and 'username' must be set");
        }

        $this->connection = new Net_SSH2($this->host, $this->port);
        
        if (is_null($this->connection)) {
            throw new BuildException("Could not establish connection to " . $this->host . ":" . $this->port . "!");
        }

        $could_auth = null;
        if ( $this->pubkeyfile ) {
            $cryptclass = 'Crypt_'.$this->crypt;
            if (!require_once('Crypt/'.$this->crypt.'.php')) { 
                throw new BuildException("To use Public or Private key files, you need to install $cryptclass from PHP Secure Communications Library (phpseclib.sourceforge.net).");
            }
            $key = new $cryptclass;
            $key->setPassword($this->privkeyfilepassphrase);
            $key->loadKey(file_get_contents($this->pubkeyfile));
            if ( $this->privkeyfile ) {
                $key->loadKey(file_get_contents($this->privkeyfile));
            }
            $could_auth = $this->connection->login($this->username, $key);
        } else {
            $could_auth = $this->connection->login($this->username, $this->password);
        }
        if (!$could_auth) {
            throw new BuildException("Could not authenticate connection!");
        }

        $output = $this->connection->exec($this->command);
        if (!$output) {
            $output = "";
        }
      
        $this->log("Executing command {$this->command}", Project::MSG_VERBOSE);
        $this->log("Result: {$output}", Project::MSG_VERBOSE);
        
        if ($this->display) {
            print($output);
        }
        
        if (!empty($this->property)) {
            $this->project->setProperty($this->property, $output);
        }
    }
    
    /**
     * Checks if the file can be required from the include path.
     * @param string $filename
     * @return string|false
     */
    private function requireable($filename)
    {
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            if (substr($path, -1) == DIRECTORY_SEPARATOR) {
                $fullpath = $path.$filename;
            } else {
                $fullpath = $path.DIRECTORY_SEPARATOR.$filename;
            }
            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }

        return false;
    }

}
?>
