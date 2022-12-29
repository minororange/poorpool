<?php


namespace Minor\Thread\Pool;


use Minor\Thread\Contract\Runnable;
use Minor\Thread\Entity\ThreadResult;
use Minor\Thread\Thread\Thread;

class NormalThreadPool
{

    private int $threadNum;
    /**
     * @var Thread[]
     */
    private array $worker = [];

    /**
     * @var Thread[]
     */
    private array $resting = [];

    /**
     * @var Runnable[]
     */
    private array $queue = [];

    private int $count = 0;

    /**
     * @var ThreadResult[]
     */
    private array $results = [];

    /**
     * @var NormalThreadPool[]
     */
    protected static array $instances = [];

    private string $name;

    protected function __construct(int $threadNum, string $name)
    {
        $this->threadNum = $threadNum;
        $this->name = $name;
    }

    public static function getInstance(string $name, int $threadNum = 0)
    {
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = new static($threadNum, $name);
        }

        return static::$instances[$name];
    }

    public function addTask(Runnable $task): NormalThreadPool
    {
        $this->queue[] = $task;

        return $this;
    }

    public function execute(): array
    {
        while ($runnable = array_shift($this->queue)) {
            if ($thread = $this->getRestingThread()) {
                $threadResult = new ThreadResult();
                $threadResult->future = $thread->setTask($runnable)->work();
                $threadResult->thread = $thread;
                $this->results[$thread->getName()] = $threadResult;
                $this->worker[$thread->getName()] = $thread;
            }
        }

        return $this->results;
    }

    private function getRestingThread(): ?Thread
    {
        $thread = array_shift($this->resting);

        if (null === $thread) {
            return $this->createNewThread();
        }

        return $thread;
    }

    private function createNewThread(): Thread
    {
        if (count($this->worker) < $this->threadNum) {
            echo "create new thread({$this->count})\n";
            $thread = new Thread($this->count, $this);
            $this->count++;

            return $thread;
        }

        while (($thread = array_shift($this->resting)) === null) {
            $count = count($this->worker);
            echo "wait for resting,worker count:{$count}\n";
            $this->waitForResult();
        }

        return $thread;
    }

    public function waitForResult()
    {
        foreach ($this->results as $result) {
            $this->complete($result->thread->getName());
        }
    }

    public function complete(string $threadName)
    {
        if (!isset($this->worker[$threadName])) {
            return;
        }
        $thread = $this->worker[$threadName];
        $result = $this->results[$threadName];
        $this->results[$threadName]->value = $result->future->value();
        unset($this->worker[$threadName]);
        $count = count($this->worker);
        echo "release thread:{$threadName},worker count:{$count}\n";
        $this->resting[] = $thread;
        echo "{$threadName} complete\n";
    }

    /**
     * @return ThreadResult[]
     */
    public function getResults(): array
    {
        return array_map(function (ThreadResult $result) {
            return [$result->thread->getTask()->getName() => $result->value];
        }, $this->results);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}