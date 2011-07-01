<?php

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-describe
 *
 * @see VersionControl_Git
 */
class GitDescribeTask extends GitBaseTask
{
    /**
     * Use any ref found in .git/refs/. See --all of git-describe
     * @var boolean
     */
    private $all = false;
    
    /**
     * Use any tag found in .git/refs/tags. See --tags of git-describe
     * @var boolean
     */
    private $tags = false;
    
    /**
     * Find tag that contains the commit. See --contains of git-describe
     * @var boolean
     */
    private $contains = false;
    
    /**
     * Use <n> digit object name. See --abbrev of git-describe
     * @var integer
     */
    private $abbrev;
    
    /**
     * Consider up to <n> most recent tags. See --candidates of git-describe
     * @var integer
     */
    private $candidates;
    
    /**
     * Always output the long format. See --long of git-describe
     * @var boolean
     */
    private $long = false;
    
    /**
     * Only consider tags matching the given pattern. See --match of git-describe
     * @var string
     */
    private $match;
    
    /**
     * Show uniquely abbreviated commit object as fallback. See --always of git-describe
     * @var boolean
     */
    private $always = false;
    
    /**
     * <committish> argument to git-describe
     * @var string
     */
    private $committish;
    
    /**
     * Property name to set with output value from git-describe
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
        $command = $client->getCommand('describe');
        $command
            ->setOption('all', $this->isAll())
            ->setOption('tags', $this->isTags())
            ->setOption('contains', $this->isContains())
            ->setOption('long', $this->isLong())
            ->setOption('always', $this->isAlways());
        
        if (null !== $this->getAbbrev()) {
            $command->setOption('abbrev', $this->getAbbrev());
        }
        if (null !== $this->getCandidates()) {
            $command->setOption('candidates', $this->getCandidates());
        }
        if (null !== $this->getMatch()) {
            $command->setOption('match', $this->getMatch());
        }
        if (null !== $this->getCommittish()) {
            $command->addArgument($this->getCommittish());
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
            sprintf('git-describe: recent tags for "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-describe output: ' . trim($output), Project::MSG_INFO);
    }
    
    public function setAll($flag)
    {
        $this->all = (bool)$flag;
    }
    
    public function getAll()
    {
        return $this->all;
    }
    
    public function isAll()
    {
        return $this->getAll();
    }
    
    public function setTags($flag)
    {
        $this->tags = (bool)$flag;
    }
    
    public function getTags()
    {
        return $this->tags;
    }
    
    public function isTags()
    {
        return $this->getTags();
    }
    
    public function setContains($flag)
    {
        $this->contains = (bool)$flag;
    }
    
    public function getContains()
    {
        return $this->contains;
    }
    
    public function isContains()
    {
        return $this->getContains();
    }
    
    public function setAbbrev($length)
    {
        $this->abbrev = (int)$length;
    }
    
    public function getAbbrev()
    {
        return $this->abbrev;
    }
    
    public function setCandidates($count)
    {
        $this->candidates = (int)$count;
    }
    
    public function getCandidates()
    {
        return $this->candidates;
    }
    
    public function setLong($flag)
    {
        $this->long = (bool)$flag;
    }
    
    public function getLong()
    {
        return $this->long;
    }
    
    public function isLong()
    {
        return $this->getLong();
    }
    
    public function setMatch($pattern)
    {
        $this->match = $pattern;
    }
    
    public function getMatch()
    {
        return $this->match;
    }
    
    public function setAlways($flag)
    {
        $this->always = (bool)$flag;
    }
    
    public function getAlways()
    {
        return $this->always;
    }
    
    public function isAlways()
    {
        return $this->getAlways();
    }
    
    public function setCommittish($object)
    {
        $this->committish = $object;
    }
    
    public function getCommittish()
    {
        return $this->committish;
    }
    
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }
}
