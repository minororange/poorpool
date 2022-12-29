<?php


namespace Minor\Thread\Demo;


use Minor\Thread\Contract\Runnable;

class TaskDemo implements Runnable
{

    private int $i;

    public function __construct(int $i)
    {
        $this->i = $i;
    }

    public function run(): int
    {
        echo "[task] {$this->i} run\n";
        $seconds = mt_rand(1, 9);
        sleep($seconds);
        echo "[task] {$this->i} done\n";
        return $seconds;
    }

    public function getName(): string
    {
        return "task({$this->i})";
    }
}