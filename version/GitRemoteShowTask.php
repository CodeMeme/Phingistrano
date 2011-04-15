<?php

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-remote-show
 *
 * @see VersionControl_Git
 */
class GitRemoteShowTask extends GitBaseTask
{
    /**
     * Remote heads are not queried first with git ls-remote <name>. See -n of git-remote-show
     * @var boolean
     */
    private $noQuery = false;
    
    /**
     * <name> argument to git-remote-show
     * @var string
     */
    private $name;
    
    /**
     * Property name to set with output value from git-remote-show
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
        
        if (null === $this->getName()) {
            throw new BuildException('"name" is required parameter');
        }
        
        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('remote show');
        $command->setOption('n', $this->isNoQuery());
        $command->addArgument($this->getName());
        
        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed');
        }
        
        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, $output);
        }
        
        $this->log(
            sprintf('git-remote-show: remotes for "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-remote-show output: ' . trim($output), Project::MSG_INFO);
    }
    
    public function setNoQuery($flag)
    {
        $this->noQuery = (bool)$flag;
    }
    
    public function getNoQuery()
    {
        return $this->noQuery;
    }
    
    public function isNoQuery()
    {
        return $this->getNoQuery();
    }
    
    public function setName($remote)
    {
        $this->name = $remote;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }
}
