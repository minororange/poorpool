<?php


namespace Minor\Thread\Thread;


use Minor\Thread\Contract\Runnable;
use Minor\Thread\Pool\NormalThreadPool;
use parallel\Future;
use parallel\Runtime;

class Thread
{
    private string $name;

    private Runtime $thread;

    private Runnable $task;

    private NormalThreadPool $threadPool;

    public function __construct(string $name, NormalThreadPool $threadPool)
    {
        $this->name = $name;
        $this->thread = new Runtime('./vendor/autoload.php');
        $this->threadPool = $threadPool;
    }


    public function work(): ?Future
    {
        $name = $this->getName();

        return $this->thread->run(static function (?Runnable $t, string $threadName, string $poolName) {
            $r = null;
            if ($t) {
                echo "[thread] {$threadName} run [{$t->getName()}]\n";
                $r = $t->run();
                NormalThreadPool::getInstance($poolName)->complete($threadName);
            }
            echo "[thread] {$threadName} complete\n";
            return $r;
        }, [$this->getTask(), $name, $this->threadPool->getName()]);
    }

    /**
     * @param Runnable $task
     * @return Thread
     */
    public function setTask(Runnable $task): Thread
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @return Runnable
     */
    public function getTask(): Runnable
    {
        return $this->task;
    }

    public function getName(): string
    {
        return $this->name;
    }
}