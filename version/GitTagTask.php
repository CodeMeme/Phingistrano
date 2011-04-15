<?php

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-tag
 *
 * @see VersionControl_Git
 */
class GitTagTask extends GitBaseTask
{
    /**
     * Make unsigned, annotated tag object. See -a of git-tag
     * @var boolean
     */
    private $annotate = false;
    
    /**
     * Make GPG-signed tag. See -s of git-tag
     * @var boolean
     */
    private $sign = false;
    
    /**
     * Make GPG-signed tag, using given key. See -u of git-tag
     * @var string
     */
    private $keySign;
    
    /**
     * Replace existing tag with given name. See -f of git-tag
     * @var boolean
     */
    private $replace = false;
    
    /**
     * Delete existing tags with given names. See -d of git-tag
     * @var boolean
     */
    private $delete = false;
    
    /**
     * Verify gpg signature of given tag names.. See -v of git-tag
     * @var boolean
     */
    private $verify = false;
    
    /**
     * List tags with names matching given pattern. See -l of git-tag
     * @var boolean
     */
    private $list = false;
    
    /**
     * Only list tags containing specified commit. See --contains of git-tag
     * @var string
     */
    private $contains;
    
    /**
     * Use given tag message. See -m of git-tag
     * @var string
     */
    private $message;
    
    /**
     * Take tag message from given file. See -F of git-tag
     * @var string
     */
    private $file;
    
    /**
     * <name> argument to git-tag
     * @var string
     */
    private $name;
    
    /**
     * <commit> argument to git-tag
     * @var string
     */
    private $commit;
    
    /**
     * <object> argument to git-tag
     * @var string
     */
    private $object;
    
    /**
     * <pattern> argument to git-tag
     * @var string
     */
    private $pattern;
    
    /**
     * Property name to set with output value from git-tag
     * @var string
     */
    private $outputProperty;
    
    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('tag');
        $command
            ->setOption('a', $this->isAnnotate())
            ->setOption('s', $this->isSign())
            ->setOption('f', $this->isReplace())
            ->setOption('d', $this->isDelete())
            ->setOption('v', $this->isVerify())
            ->setOption('l', $this->isList());
        
        if (null !== $this->getKeySign()) {
            $command->setOption('u', $this->getKeySign());
        }
        if (null !== $this->getContains()) {
            $command->setOption('contains', $this->getContains());
        }
        if (null !== $this->getMessage()) {
            $command->setOption('m', $this->getMessage());
        }
        if (null !== $this->getFile()) {
            $command->setOption('F', $this->getFile());
        }
        
        // Use 'name' arg, if relevant
        if (null !== $this->getKeySign() || $this->isAnnotate() || $this->isSign() || $this->isDelete() || $this->isVerify()) {
            if (null !== $this->getName()) {
                $command->addArgument($this->getName());
            }
        }
        
        if (null !== $this->getKeySign() || $this->isAnnotate() || $this->isSign()) {
            // Require a tag message or file
            if (null === $this->getMessage() && null === $this->getFile()) {
                throw new BuildException('"message" or "file" required to make a tag');
            }
            
            // Use 'commit' or 'object' args, if relevant
            if (null !== $this->getCommit()) {
                $command->addArgument($this->getCommit());
            }
            if (null !== $this->getObject()) {
                $command->addArgument($this->getObject());
            }
        }

        // Use 'pattern' arg, if relevant
        if ($this->isList()) {
            if (null !== $this->getPattern()) {
                $command->addArgument($this->getPattern());
            }
        }

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed');
        }

        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, $output);
        }

        $this->log(
            sprintf('git-tag: tags for "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-tag output: ' . trim($output), Project::MSG_INFO);
    }
    
    public function setAnnotate($flag)
    {
        $this->annotate = (bool)$flag;
    }
    
    public function getAnnotate()
    {
        return $this->annotate;
    }
    
    public function isAnnotate()
    {
        return $this->getAnnotate();
    }

    public function setSign($flag)
    {
        $this->sign = (bool)$flag;
    }
    
    public function getSign()
    {
        return $this->sign;
    }
    
    public function isSign()
    {
        return $this->getSign();
    }

    public function setKeySign($keyId)
    {
        $this->keySign = $keyId;
    }
    
    public function getKeySign()
    {
        return $this->keySign;
    }

    public function setReplace($flag)
    {
        $this->replace = (bool)$flag;
    }
    
    public function getReplace()
    {
        return $this->replace;
    }
    
    public function isReplace()
    {
        return $this->getReplace();
    }

    public function setDelete($flag)
    {
        $this->delete = (bool)$flag;
    }
    
    public function getDelete()
    {
        return $this->delete;
    }
    
    public function isDelete()
    {
        return $this->getDelete();
    }

    public function setVerify($flag)
    {
        $this->verify = (bool)$flag;
    }
    
    public function getVerify()
    {
        return $this->verify;
    }
    
    public function isVerify()
    {
        return $this->getVerify();
    }

    public function setList($flag)
    {
        $this->list = (bool)$flag;
    }
    
    public function getList()
    {
        return $this->list;
    }
    
    public function isList()
    {
        return $this->getList();
    }

    public function setContains($commit)
    {
        $this->contains = $commit;
    }
    
    public function getContains()
    {
        return $this->contains;
    }

    public function setMessage($msg)
    {
        $this->message = $msg;
    }
    
    public function getMessage()
    {
        return $this->message;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }
    
    public function getFile()
    {
        return $this->file;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setCommit($commit)
    {
        $this->commit = $commit;
    }
    
    public function getCommit()
    {
        return $this->commit;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }
    
    public function getObject()
    {
        return $this->object;
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }
    
    public function getPattern()
    {
        return $this->pattern;
    }
    
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }
}
