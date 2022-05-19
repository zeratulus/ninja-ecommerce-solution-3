<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 11.01.20
 * Time: 22:14
 */
class ModelSupportSupport extends Model {

    public static $PROJECT_ID_ORDERS = 1;
    public static $PROJECT_ID_CONTACTS = 2;
    public static $PROJECT_ID_FEEDBACK = 3;

    public function addTicket(Support\Ticket $ticket)
    {
        $sql = "INSERT INTO `tickets`(`title`, `description`, `start`, `finish`, `status`, `created_by_uid`, `delegated_to_uid`, `parent_task_id`, `project_id`, `category_id`, `priority`, `deadline`, `created`) VALUES ('{$ticket->getTitle()}', '{$ticket->getDescription()}', '{$ticket->getStart()}', '{$ticket->getFinish()}', '{$ticket->getStatus()}', '{$ticket->getCreatedByUid()}', '{$ticket->getDelegatedToUid()}', '{$ticket->getParentTaskId()}', '{$ticket->getProjectId()}', '{$ticket->getCategoryId()}', '{$ticket->getPriority()}', '{$ticket->getDeadline()}', '{$ticket->getCreated()}');";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

}