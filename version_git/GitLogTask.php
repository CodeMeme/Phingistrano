<?php

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-log
 *
 * @see VersionControl_Git
 */
class GitLogTask extends GitBaseTask
{
    /**
     * Generate a diffstat. See --stat of git-log
     * @var string|boolean
     */
    private $stat = false;
    
    /**
     * Names + status of changed files. See --name-status of git-log
     * @var boolean
     */
    private $nameStatus = false;
    
    /**
     * Number of commits to show. See -<n>|-n|--max-count of git-log
     * @var integer
     */
    private $maxCount;
    
    /**
     * Don't show commits with more than one parent. See --no-merges of git-log
     * @var boolean
     */
    private $noMerges = false;
    
    /**
     * Commit format. See --format of git-log
     * @var string
     */
    private $format = 'medium';
    
    /**
     * Date format. See --date of git-log
     * @var string
     */
    private $date;
    
    /**
     * <since> argument to git-log
     * @var string
     */
    private $sinceCommit;

    /**
     * <until> argument to git-log
     * @var string
     */
    private $untilCommit;
    
    /**
     * <path> arguments to git-log
     * Accepts one or more paths delimited by PATH_SEPARATOR
     * @var string
     */
    private $paths;
    
    /**
     * Property name to set with output value from git-log
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
        $command = $client->getCommand('log');
        $command
            ->setOption('stat', $this->getStat())
            ->setOption('name-status', $this->isNameStatus())
            ->setOption('no-merges', $this->isNoMerges())
            ->setOption('format', $this->getFormat());
        
        if (null !== $this->getMaxCount()) {
            $command->setOption('max-count', $this->getMaxCount());
        }
        
        if (null !== $this->getDate()) {
            $command->setOption('date', $this->getDate());
        }
        
        if (null !== $this->getSince() && null !== $this->getUntil()) {
            $command->addArgument($this->getSince() . '..' . $this->getUntil());
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
            sprintf('git-log: commit log for "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-log output: ' . trim($output), Project::MSG_INFO);
    }
    
    public function setStat($stat)
    {
        $this->stat = $stat;
    }
    
    public function getStat()
    {
        return $this->stat;
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
    
    public function setMaxCount($count)
    {
        $this->maxCount = (int)$count;
    }
    
    public function getMaxCount()
    {
        return $this->maxCount;
    }
    
    public function setNoMerges($flag)
    {
        $this->noMerges = (bool)$flag;
    }
    
    public function getNoMerges()
    {
        return $this->noMerges;
    }
    
    public function isNoMerges()
    {
        return $this->getNoMerges();
    }
    
    public function setFormat($format)
    {
        $this->format = $format;
    }
    
    public function getFormat()
    {
        return $this->format;
    }
    
    public function setDate($date)
    {
        $this->date = $date;
    }
    
    public function getDate()
    {
        return $this->date;
    }
    
    public function setSince($since)
    {
        $this->sinceCommit = $since;
    }

    public function getSince()
    {
        return $this->sinceCommit;
    }
    
    public function setUntil($until)
    {
        $this->untilCommit = $until;
    }

    public function getUntil()
    {
        return $this->untilCommit;
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
