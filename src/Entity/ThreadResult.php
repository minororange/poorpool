<?php


namespace Minor\Thread\Entity;


use Minor\Thread\Thread\Thread;
use parallel\Future;

class ThreadResult
{
    public ?Thread $thread;

    public ?Future $future;

    public  $value;
}