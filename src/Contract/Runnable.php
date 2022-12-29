<?php


namespace Minor\Thread\Contract;


interface Runnable
{
    public function run();

    public function getName(): string;
}