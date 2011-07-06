<?php

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-diff
 *
 * @see VersionControl_Git
 */
class GitDiffTask extends GitBaseTask
{
    /**
     * Generate a diffstat. See --stat of git-diff
     * @var string|boolean
     */
    private $stat = false;
    
    /**
     * Output last line of the stat format. See --shortstat of git-diff
     * @var boolean
     */
    private $shortstat = false;
    
    /**
     * Names + status of changed files. See --name-status of git-diff
     * @var boolean
     */
    private $nameStatus = false;
    
    /**
     * Ignore changes to submodules. See --ignore-submodules of git-diff
     * @var boolean
     */
    private $ignoreSubmodules = false;
    
    /**
     * View changes staged for next commit. See --cached of git-diff
     * @var boolean
     */
    private $cached = false;
    
    /**
     * <commit> argument(s) to git-diff
     * @var string
     */
    private $commits;
    
    /**
     * <path> arguments to git-diff
     * Accepts one or more paths delimited by PATH_SEPARATOR
     * @var string
     */
    private $paths;
    
    /**
     * Property name to set with output value from git-diff
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
        $command = $client->getCommand('diff');
        $command
            ->setOption('stat', $this->getStat())
            ->setOption('shortstat', $this->isShortstat())
            ->setOption('name-status', $this->isNameStatus())
            ->setOption('ignore-submodules', $this->isIgnoreSubmodules())
            ->setOption('cached', $this->isCached());
        
        if (null !== $this->getCommits()) {
            $command->addArgument($this->getCommits());
        }
        
        if (null !== $this->getPaths()) {
            $command->addArgument('--');
            $paths = explode(PATH_SEPARATOR, $this->getPaths());
            foreach ($paths as $path) {
                $command->addArgument($path);
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
            sprintf('git-diff: commit diffs for "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-diff output: ' . trim($output), Project::MSG_INFO);
    }
    
    public function setStat($stat)
    {
        $this->stat = $stat;
    }
    
    public function getStat()
    {
        return $this->stat;
    }
    
    public function setShortstat($flag)
    {
        $this->shortstat = (bool)$flag;
    }
    
    public function getShortstat()
    {
        return $this->shortstat;
    }
    
    public function isShortstat()
    {
        return $this->getShortstat();
    }
    
    public function setNameStatus($flag)
    {
        $this->nameStatus = (boolean)$flag;
    }
    
    public function getNameStatus()
    {
        return $this->nameStatus;
    }
    
    public function isNameStatus()
    {
        return $this->getNameStatus();
    }
    
    public function setIgnoreSubmodules($flag)
    {
        $this->ignoreSubmodules = (bool)$flag;
    }
    
    public function getIgnoreSubmodules()
    {
        return $this->ignoreSubmodules;
    }
    
    public function isIgnoreSubmodules()
    {
        return $this->getIgnoreSubmodules();
    }
    
    public function setCached($flag)
    {
        $this->cached = (bool)$flag;
    }
    
    public function getCached()
    {
        return $this->cached;
    }
    
    public function isCached()
    {
        return $this->getCached();
    }
    
    public function setCommits($commits)
    {
        $this->commits = $commits;
    }

    public function getCommits()
    {
        return $this->commits;
    }
    
    public function setPaths($paths)
    {
        $this->paths = $paths;
    }
    
    public function getPaths()
    {
        return $this->paths;
    }
    
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }
}
