<?php
namespace App\EventListener;

use App\Entity\Task;

/**
 * Service to automatically change the value of status attribute of a task to reflect it's current state.
 */
class TaskUpdateStatus
{
    public function updateStatus(Task $task){
        if($task->getDatetimeCompleted() !== null)      {$task->setStatus(Task::DONE);}
        elseif($task->getDatetimeAccepted() !== null)   {$task->setStatus(Task::ONGOING);}
        elseif($task->isRejected())                     {$task->setStatus(Task::REJECTED);}
        elseif($task->getAssignedTo() !== null)         {$task->setStatus(Task::ASSIGNED);}
        else                                            {$task->setStatus(Task::UNASSIGNED);}
    }

}