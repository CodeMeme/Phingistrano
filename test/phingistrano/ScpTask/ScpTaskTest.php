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

require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/ScpTask.php';
require_once dirname(__FILE__) . '/ScpTestHelper.php';

/**
 * @author Jesse Greathouse <jesse.greathouse@gmail.com>
 * @package phing.tasks.ext
 */
class ScpTaskTest extends BuildFileTest { 

    protected $mock;
    
    protected $privatekey = '-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: AES-128-CBC,47E7F9DC303BB5469E67DB116B079C38

NqjHYcSKtUCNP0TiAC7+1O6Xd+L457ks0RGawZBFkZa4x6gUHdSQ8Gt9rErfLhU2
wVY9/X1gPafVOlefha4pljTnNuPfuccn4Dl8QghCjMr7rlaqk+QV3sUK9OvxpLCC
FQ5n+Cqqz5CfH9XgCHIOI2dz2e0niI2TyeTObKOsm0Lu7A0Yt2N+aGee/Uj5HQb5
X/JwT2GJ2F4vJH7MlFgexdQaJbsavRhn6Ln5C4S4bxnCy2MfZZ1jprgrFYJtiJ+h
cmLsFYbtqMue/8BOh5Cjx+McRsHZwe6WaUmTQ1kWkqDXzREFXse4IggpUZtD4ejc
3Qx9ZMda/gjS3yWwQEoAviEhtMJa1mzTpX6MdSD9YHcxjTSmBhRL/xrjvV/4eJEm
4mVhCaQgWZmq4Njir/vIELAGb3FF8rZUKziD5n4ypU1/A3KmgS2NQPK2HPkelehc
TJOW5oDw85SEzs64Eq3NKXEWfWcLpSlLdx7BRUAP211/YLVbgCxccd7f3RvgAzht
EfJfVTtv1plrMxTA3J2N02brw7mp9F5y1sZf0snWjqv4SCi4OkNIHfDgHT82zJRJ
acHcYskdWfruOwMlMyls2cyjQm8GqalqUehmch54B9YdI3vIrL1jNts0NH7IJOuV
qGTAA+P4/DC6Gk+1Gk9ZH5hDqSKvz/Ja/RjhJJZC93QRKaJvy+oqKicYnPXa5rve
9UiJPvv6ZFeoqugf6AyKdAIlXSK9sjOormpSYBukUlBf/f+QDgt9E22Fu2xAOXBK
ZtbMCFoKRpRZEFlWyggdsWym+61sofBJeRlk9vp/geBKEdOsL4YkPYWIZxqkPF8V
Z0QqEPEeuiEtsFZ/CcueEGUmUwQ3UCN9vliGvL6YYmq8UubS+zzCXEJ4FjCT10S8
NXhBzy+jOLIYNzX/mpjq4nGXnKlDAoxuKq6f8MlsT/am98xuCzTyYl7uDzHX2CRn
/EY5MiIByRBr0l+HWEI/JdzTTPxLzxnm+QaQcaYotWw4Wjt8KTDa1x9MfQXEffuN
74XX+lyhzGCgU1qzFjdFEYAxHriMkaH6BxRnNLtfNQmAaVmIhDk1rZVFuRHjV6OX
ex5VR1srbRKk+/VWwBxzsQaxz+aCVhqTcssdHhjc6xTZo/UZIrrtZXf4PWB4/FWM
qW36FzdRpRUJ5Yxs3/RtN1Wv2Wk63W09T6XRaIYNAb1J4G1HinFN/4UQF+bpT4W+
aG/jmBcd1MQt+v2+x+zIZBLx0swQLaENjS6/LD4Tv7u/nHTM0s2z6kpOJ6jqAV+5
uQw8EuRaJG4MKlbWx5iBo3da10q2j+ObEa6K89osVlZ+hlGjVeIKZ8m4j4u1mjE5
JwEaFEOTdPcyHC9M9kO94/d9GR5a3meAp+DeRkw61lzG4hYzBPSeKxXCzjWBiOcE
3nz3cs/3u4iZfuUgLYqsKegwDRXctbiIiPLxftVhxt2I5rm5SJmXRmR8SHas79eN
cF+PEMTNtr/mamZ7j1z4mllDqJ4U1IF6xVJqmfa+ekpro6ajBCqRniKzqIRwspXA
yuWUf77/0QTuT+oW+sj6VZK7XY2N69McPZcgFVDLDSy6rDRd1ketNA/D8UqwKFa4
-----END RSA PRIVATE KEY-----';
    
    protected $publickey = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQC9j0bxEAEBSEuqwSUhlUXVT14a7CDiqqP2xC2UGYq910ZSr6aydaSJDs2PTLCvTR5v4pffCbtptOcDkKtWsQKCQvAXHjNlXneKqzUY8SiV9kN8kcaWT9H0fG+X4EBIcyRC3iRcdNJU1mMEc4pKCYc2JqE+w9OsYjo0jK58h0lb/bMvcmN76/2U6nRPMG5wYxVwuXAfpCUX2MrmjmZtUjunYT/8Y7fLZmAueauSJYGU8gNb1pq02Ov8bw+7SxmnRnr0Re15CQaNO4SClQol8SJTM2MVDqSojSsd8oyINX+4iyyiW0ljBPtkBEyin1SFhXp8RupT13eugIG+fGekaoPP phing@jessegreathouse.us';
    
    
    
    public function setUp() { 
        $this->configureProject(__DIR__ . "/ScpTaskTest.xml");
        $this->mock = $this->getMockForAbstractClass('ScpTask');
        
        if (is_readable(PHING_TEST_TMP . '/scp')) {
            // make sure we purge previously created directory
            // if left-overs from previous run are found
            ScpTestHelper::rmdir(PHING_TEST_TMP . '/scp');
        }
        // set temp directory used by test cases
        mkdir(PHING_TEST_TMP . '/scp');
        
        $privatekeyfile = PHING_TEST_TMP . '/scp/id_rsa';
        $publickeyfile = PHING_TEST_TMP . '/scp/id_rsa.pub';
        
        //set up pub and private key files
        $fv = fopen($privatekeyfile, 'w+'); 
        fwrite($fv, $this->privatekey);
        $fb = fopen($publickeyfile, 'w+'); 
        fwrite($fb, $this->publickey);

    }

    public function tearDown()
    {
        ScpTestHelper::rmdir(PHING_TEST_TMP . '/scp');
    }
    
    public function testInitialization()
    {
        $this->assertInstanceOf('ScpTask', $this->mock);
    }
    
    public function testMutators()
    {
        // host
        $host = $this->mock->getHost();
        $this->mock->setHost('my-new-host');
        $this->assertEquals('my-new-host', $this->mock->getHost());
        $this->mock->setHost($host);
        
        // port
        $port = $this->mock->getPort();
        $this->mock->setPort('my-new-port');
        $this->assertEquals('my-new-port', $this->mock->getPort());
        $this->mock->setPort($port);
        
        // username
        $username = $this->mock->getUsername();
        $this->mock->setUsername('my-new-username');
        $this->assertEquals('my-new-username', $this->mock->getUsername());
        $this->mock->setUsername($username);
        
        // password
        $password = $this->mock->getPassword();
        $this->mock->setPassword('my-new-password');
        $this->assertEquals('my-new-password', $this->mock->getPassword());
        $this->mock->setPassword($password);
        
        // pubkeyfile
        $pubkeyfile = $this->mock->getPubkeyfile();
        $this->mock->setPubkeyfile('my-new-pubkeyfile');
        $this->assertEquals('my-new-pubkeyfile', $this->mock->getPubkeyfile());
        $this->mock->setPubkeyfile($pubkeyfile);
        
        // privkeyfile
        $privkeyfile = $this->mock->getPrivkeyfile();
        $this->mock->setPrivkeyfile('my-new-privkeyfile');
        $this->assertEquals('my-new-privkeyfile', $this->mock->getPrivkeyfile());
        $this->mock->setPrivkeyfile($privkeyfile);
        
        // privkeyfilepassphrase
        $privkeyfilepassphrase = $this->mock->getPrivkeyfilepassphrase();
        $this->mock->setPrivkeyfilepassphrase('my-new-privkeyfilepassphrase');
        $this->assertEquals('my-new-privkeyfilepassphrase', $this->mock->getPrivkeyfilepassphrase());
        $this->mock->setPrivkeyfilepassphrase($privkeyfilepassphrase);
        
        // sshlib
        $sshlib = $this->mock->getSshlib();
        $this->mock->setSshlib('my-new-sshlib');
        $this->assertEquals('my-new-sshlib', $this->mock->getSshlib());
        $this->mock->setSshlib($sshlib);
        
        // crypt
        $crypt = $this->mock->getCrypt();
        $this->mock->setCrypt('my-new-crypt');
        $this->assertEquals('my-new-crypt', $this->mock->getCrypt());
        $this->mock->setCrypt($crypt);
        
        // autocreate
        $autocreate = $this->mock->getAutocreate();
        $this->mock->setAutocreate(true);
        $this->assertEquals(true, $this->mock->getAutocreate());
        $this->mock->setAutocreate($autocreate);
        
        // todir
        $todir = $this->mock->getTodir();
        $this->mock->setTodir('my-new-todir');
        $this->assertEquals('my-new-todir', $this->mock->getTodir());
        $this->mock->setTodir($todir);
        
        // file
        $file = $this->mock->getFile();
        $this->mock->setFile('my-new-file');
        $this->assertEquals('my-new-file', $this->mock->getFile());
        $this->mock->setFile($file);
        
        // fetch
        $fetch = $this->mock->getFetch();
        $this->mock->setFetch(true);
        $this->assertEquals(true, $this->mock->getFetch());
        $this->mock->setFetch($fetch);
    }
    
    public function testDefaultSshlib()
    {
        $uploadFile = PHING_TEST_TMP . '/scp/testUpload';
        if (file_exists($uploadFile)) {
            unlink($uploadFile);
        }
        $fh = fopen($uploadFile, 'w+');
        while (($size = filesize($uploadFile)) < 1024) {
            fwrite($fh, rand(0,9));
            clearstatcache();
        }
        $this->executeTarget('defaultSshlib');
        $this->assertInLogs('Copied 1 file');
    }
    
    public function testAuthNetssh()
    {
        $resultFile = PHING_TEST_TMP . '/scp/scpFetch';
        if (file_exists($resultFile)) {
            unlink($resultFile);
        }
        $this->executeTarget('authNetssh');
        $this->assertEquals('phing', trim(file_get_contents($resultFile)));
    }
    
    public function testAuthSsh2()
    {   
        $resultFile = PHING_TEST_TMP . '/scp/scpFetch';
        if (file_exists($resultFile)) {
            unlink($resultFile);
        }
        $this->executeTarget('authSsh2');
        $this->assertEquals('phing', trim(file_get_contents($resultFile)));
    }
    
    public function testAllParamsSet()
    {
        $resultFile = PHING_TEST_TMP . '/scp/scpFetch';
        if (file_exists($resultFile)) {
            unlink($resultFile);
        }
        $this->executeTarget('allParamsSet');
        $this->assertEquals('phing', trim(file_get_contents($resultFile)));
    }
    
    public function testUpload()
    {
        $uploadFile = PHING_TEST_TMP . '/scp/testUpload';
        if (file_exists($uploadFile)) {
            unlink($uploadFile);
        }
        $fh = fopen($uploadFile, 'w+');
        while (($size = filesize($uploadFile)) < 49919677) {
            fwrite($fh, rand(0,9));
            clearstatcache();
        }
        $this->executeTarget('testUpload');
        $this->assertInLogs('Copied 1 file');
    }
    
    public function testFilesetUpload()
    {
        $uploadFile1 = PHING_TEST_TMP . '/scp/testFilesetUpload1.fs';
        $uploadFile2 = PHING_TEST_TMP . '/scp/testFilesetUpload2.fs';
        if (file_exists($uploadFile1)) {
            unlink($uploadFile1);
        }
        if (file_exists($uploadFile2)) {
            unlink($uploadFile2);
        }
        $fh1 = fopen($uploadFile1, 'w+');
        while (($size = filesize($uploadFile1)) < 1024) {
            fwrite($fh1, rand(0,9));
            clearstatcache();
        }
        $fh2 = fopen($uploadFile2, 'w+');
        while (($size = filesize($uploadFile2)) < 1024) {
            fwrite($fh2, rand(0,9));
            clearstatcache();
        }
        $this->executeTarget('testFilesetUpload');
        $this->assertInLogs('Copied 2 file');
    }

}
