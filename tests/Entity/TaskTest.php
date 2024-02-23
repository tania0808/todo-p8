<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testCanGetAndSetData() {
        $task = new Task();
        $task->setTitle('Emails');
        $task->setContent('Send emails to clients');

        $this->assertEquals('Emails', $task->getTitle());
        $this->assertFalse($task->isDone());
        $this->assertEquals('Send emails to clients', $task->getContent());
    }


    public function testToggleTask()
    {
        $task = new Task();
        $task->setTitle('Emails');
        $task->setContent('Send emails to clients');

        $task->toggle(true);

        $this->assertTrue($task->isDone());
    }

}