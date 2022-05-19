<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 15.09.19
 * Time: 21:28
 */

class ModelSupportSupport extends Model {

    //Project
    public function addProject(\Support\Project $project)
    {
        $sql = "INSERT INTO `projects`(`title`, `description`, `created`, `status`, `public`, `color`, `icon`, `timezone`) VALUES ('{$project->getTitle()}', '{$project->getDescription()}', '{$project->getCreated()}', '{$project->getStatus()}', '{$project->getIsPublic()}', '{$project->getColor()}', '{$project->getIcon()}', '{$project->getTimezone()}');";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    public function editProject(\Support\Project $project)
    {
        $sql = "UPDATE `projects` SET `title`='{$project->getTitle()}',`description`='{$project->getDescription()}',`created`='{$project->getCreated()}',`status`='{$project->getStatus()}',`public`='{$project->getIsPublic()}',`color`='{$project->getColor()}',`icon`='{$project->getIcon()}',`timezone`='{$project->getTimezone()}' WHERE `id`='{$project->getId()}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function removeProject(int $id)
    {
        $sql = "DELETE FROM `projects` WHERE id='{$id}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function getProjects()
    {
        //TODO: Filter for projects
        $sql = "SELECT * FROM `projects`";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getProjectById(int $id)
    {
        $sql = "SELECT * FROM `projects` WHERE id='{$id}' LIMIT 1";
        $results = $this->getDb()->query($sql);
        return $results->row;
    }

    //Project User Permissions - Project Settings Tab
    public function getProjectUsers(int $project_id)
    {
        $sql = "SELECT * FROM `project_permissions` WHERE project_id='{$project_id}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getUserProjectsIds(int $uid)
    {
        $sql = "SELECT * FROM `project_permissions` WHERE uid='{$uid}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getProjectsByPermissions(int $uid)
    {
        $sql = "SELECT * FROM `project_permissions` pp LEFT JOIN `projects` p ON (pp.project_id = p.id) WHERE pp.uid='{$uid}' AND pp.permission LIKE '1___'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getUserPermissionsByProject(int $project_id, int $uid)
    {
        $sql = "SELECT * FROM `project_permissions` WHERE uid='{$uid}' AND project_id='{$project_id}' LIMIT 1";
        $results = $this->getDb()->query($sql);
        return $results->row;
    }

    public function addUserToProject(int $project_id, int $uid, string $permission)
    {
        $sql = "INSERT INTO `project_permissions`(`project_id`, `uid`, `permission`) VALUES ('{$project_id}', '{$uid}', '{$permission}')";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    public function deleteUserFromProject(int $project_id, int $uid)
    {
        $sql = "DELETE FROM `project_permissions` WHERE project_id='{$project_id}' AND uid='{$uid}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function setProjectUserPermission(int $project_id, int $uid, string $permission)
    {
        $sql = "UPDATE `project_permissions` SET `permission`='{$permission}' WHERE project_id='{$project_id}' AND uid='{$uid}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }
    
    //Ticket Categories
    public function addCategory(Support\Category $category)
    {
        $sql = "INSERT INTO `ticket_categories`(`project_id`, `title`, `description`) VALUES ('{$category->getProjectId()}', '{$category->getTitle()}', '{$category->getDescription()}')";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    public function editCategory(Support\Category $category)
    {
        $sql = "UPDATE `ticket_categories` SET `project_id`='{$category->getProjectId()}',`title`='{$category->getTitle()}',`description`='{$category->getDescription()}' WHERE `id`='{$category->getId()}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function removeCategory(int $id)
    {
        $sql = "DELETE FROM `ticket_categories` WHERE id='{$id}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function getCategories()
    {
        $sql = "SELECT * FROM `ticket_categories`;";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getCategoriesByProjectId(int $id)
    {
        $sql = "SELECT * FROM `ticket_categories` WHERE project_id = '{$id}';";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getCategoryById(int $id)
    {
        $sql = "SELECT * FROM `ticket_categories` WHERE id={$id} LIMIT 1;";
        $results = $this->getDb()->query($sql);
        return $results->row;
    }

    //Ticket Statuses

    public function getTicketStatuses(): array
    {
        $this->language->load('support/tickets');

        $data = array();

        $new = new \Support\Status($this->registry);
        $new->setId(\Support\Status::NEW_ID);
        $new->setColor('primary-color');
        $new->setTitle($this->language->get('status_new'));
        $data[\Support\Status::NEW_ID] = $new;

        $inProgress = new \Support\Status($this->registry);
        $inProgress->setId(\Support\Status::PROGRESS_ID);
        $inProgress->setColor('warning-color');
        $inProgress->setTitle($this->language->get('status_in_progress'));
        $data[\Support\Status::PROGRESS_ID] = $inProgress;

        $completed = new \Support\Status($this->registry);
        $completed->setId(\Support\Status::COMPLETED_ID);
        $completed->setColor('success-color');
        $completed->setTitle($this->language->get('status_completed'));
        $data[\Support\Status::COMPLETED_ID] = $completed;

        $canceled = new \Support\Status($this->registry);
        $canceled->setId(\Support\Status::CANCELED_ID);
        $canceled->setColor('default-color');
        $canceled->setTitle($this->language->get('status_canceled'));
        $data[\Support\Status::CANCELED_ID] = $canceled;

        return $data;
    }

    public function changeTicketStatus(int $ticket_id, int $status)
    {
        $old = $this->getTicketById($ticket_id);
        $ticket = new Support\Ticket($this->registry);
        $ticket->mapData($old);

        $now = nowMySQLTimestamp();
        $sql = "UPDATE `tickets` SET `status`={$status} ";
        if ( ($status == Support\Status::COMPLETED_ID) && ($old['status'] != $status) ) {
            $sql .= ", `finish` = '{$now}' ";
        } elseif ( ($status == Support\Status::PROGRESS_ID) && ($old['status'] != $status) ) {
            $sql .= ", `start` = '{$now}' ";
        }
        $sql .= " WHERE id = '{$ticket_id}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

//    public function getTicketStatuses(int $project_id)
//    {
//        $sql = "SELECT * FROM `ticket_statuses` WHERE project_id = '{$project_id}';";
//        $results = $this->getDb()->query($sql);
//        return $results->rows;
//    }

//    public function addTicketStatus(Support\Status $status)
//    {
//        $sql = "INSERT INTO `ticket_statuses`(`project_id`, `title`, `color`) VALUES ('{$status->getProjectId()}', '{$status->getTitle()}', '{$status->getColor()}')";
//        $this->getDb()->query($sql);
//        return $this->getDb()->getLastId();
//    }
//
//    public function deleteTicketStatus(int $id)
//    {
//        $sql = "DELETE FROM `ticket_statuses` WHERE id = '{$id}'";
//        $this->getDb()->query($sql);
//        return $this->getDb()->countAffected();
//    }

    //Tickets
    public function addTicket(Support\Ticket $ticket)
    {
        $sql = "INSERT INTO `tickets`(`title`, `description`, `start`, `finish`, `status`, `created_by_uid`, `delegated_to_uid`, `parent_task_id`, `project_id`, `category_id`, `priority`, `deadline`, `created`) VALUES ('{$ticket->getTitle()}', '{$ticket->getDescription()}', '{$ticket->getStart()}', '{$ticket->getFinish()}', '{$ticket->getStatus()}', '{$ticket->getCreatedByUid()}', '{$ticket->getDelegatedToUid()}', '{$ticket->getParentTaskId()}', '{$ticket->getProjectId()}', '{$ticket->getCategoryId()}', '{$ticket->getPriority()}', '{$ticket->getDeadline()}', '{$ticket->getCreated()}');";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    public function editTicket(Support\Ticket $ticket)
    {
        //if status == completed and old status != new status then set Finish date
        $old = $this->getTicketById($ticket->getId());
        if ( ($ticket->getStatus() == Support\Status::COMPLETED_ID) && ($old['status'] != $ticket->getStatus()) ) {
            $ticket->setFinish(nowMySQLTimestamp());
        }

        $sql = "UPDATE `tickets` SET `title`='{$ticket->getTitle()}',`description`='{$ticket->getDescription()}',`start`='{$ticket->getStart()}',`finish`='{$ticket->getFinish()}',`status`='{$ticket->getStatus()}',`created_by_uid`='{$ticket->getCreatedByUid()}',`delegated_to_uid`='{$ticket->getDelegatedToUid()}',`parent_task_id`='{$ticket->getParentTaskId()}',`project_id`='{$ticket->getProjectId()}',`category_id`='{$ticket->getCategoryId()}',`priority`='{$ticket->getPriority()}',`deadline`='{$ticket->getDeadline()}',`created`='{$ticket->getCreated()}' WHERE `id`='{$ticket->getId()}';";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function removeTicket(int $id)
    {
        $sql = "DELETE FROM `tickets` WHERE id='{$id}';";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function getTicketChildIds(int $id)
    {
        $sql = "SELECT id FROM `tickets` WHERE parent_task_id = '{$id}';";
        $result = $this->getDb()->query($sql);
        return $result->rows;
    }

    public function getTicketById(int $id)
    {
        $sql = "SELECT * FROM `tickets` WHERE id = '{$id}' LIMIT 1;";
        $result = $this->getDb()->query($sql);
        return $result->row;
    }

    public function getTickets(Support\TicketFilter $filter)
    {
        $sql = "SELECT * FROM `tickets`";

        if ($filter->isFilter()) {
            $sql .= " WHERE ";

            $filterParts = array();

            if ($filter->getProjectId() >= 0) {
                $filterParts[] = " (project_id = '{$filter->getProjectId()}') ";
            }
            if ($filter->getCategoryId() >= 0) {
                $filterParts[] = " (category_id = '{$filter->getCategoryId()}') ";
            }
            if ($filter->getStatus() > 0) {
                $filterParts[] = " (status = '{$filter->getStatus()}') ";
            } else {
                $filterParts[] = " (status > 0) ";
            }
            if ($filter->getCreatedByUid() >= 0) {
                $filterParts[] = " created_by_uid = '{$filter->getCreatedByUid()}' ";
            }
            if ($filter->getDelegatedToUid() >= 0) {
                $filterParts[] = " delegated_to_uid = '{$filter->getDelegatedToUid()}' ";
            }
            if (!empty($filter->getTitle())) {
                $filterParts[] = " (title LIKE '%{$filter->getTitle()}%')";
            }
            if ($filter->getLimit() > 0) {
                $filterParts[] = " (title LIKE '%{$filter->getTitle()}%')";
            }

            //TODO: Filters By DateTimes
//            if ((!empty($filter->getStart())) && (!empty($filter->getFinish()))) {
//                $filterParts[] = " start >= '{$filter->getStart()}'  ";
//            } elseif () {
//                $filterParts[] = " finish = '{$filter->getFinish()}' ";
//            }

            $sql .= implode(' AND ', $filterParts);

            $sql .= " ORDER BY {$filter->getSortField()} {$filter->getSortDirection()} ";


            if (!$filter->isOnePage()) {
                if ($filter->getPage() <= 0) {
                    $filter->setPage(1);
                }

                if ($filter->getLimit() < 1) {
                    $filter->setLimit(20);
                }

                $sql .= " LIMIT " . (($filter->getPage() - 1) * $filter->getLimit()) . "," . (int)$filter->getLimit();
            }

        }

        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getTicketsTotal(Support\TicketFilter $filter)
    {
        $sql = "SELECT COUNT(id) AS total FROM `tickets`";

        if ($filter->isFilter()) {
            $sql .= " WHERE ";

            $filterParts = array();

            if ($filter->getProjectId() >= 0) {
                $filterParts[] = " (project_id = '{$filter->getProjectId()}') ";
            }
            if ($filter->getCategoryId() >= 0) {
                $filterParts[] = " (category_id = '{$filter->getCategoryId()}') ";
            }
            if ($filter->getStatus() > 0) {
                $filterParts[] = " (status = '{$filter->getStatus()}') ";
            } else {
                $filterParts[] = " (status > 0) ";
            }
            if ($filter->getCreatedByUid() >= 0) {
                $filterParts[] = " created_by_uid = '{$filter->getCreatedByUid()}' ";
            }
            if ($filter->getDelegatedToUid() >= 0) {
                $filterParts[] = " delegated_to_uid = '{$filter->getDelegatedToUid()}' ";
            }
            if (!empty($filter->getTitle())) {
                $filterParts[] = " (title LIKE '%{$filter->getTitle()}%')";
            }

            $sql .= implode(' AND ', $filterParts);
        }

        $results = $this->getDb()->query($sql);
        return $results->row['total'];
    }

    public function getTicketsHeatMap(int $project_id)
    {
        $sql = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(created, '%Y-%m-%d 00:00:00')) AS created, UNIX_TIMESTAMP(DATE_FORMAT(start, '%Y-%m-%d 00:00:00')) AS start, UNIX_TIMESTAMP(DATE_FORMAT(finish, '%Y-%m-%d 00:00:00')) AS finish FROM `tickets` WHERE project_id = {$project_id}";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    // ------------------------- Ticket Comments --------------------------------

    public function addComment(\Support\Comment $comment)
    {
        $sql = "INSERT INTO `ticket_comments`(`ticket_id`, `uid`, `text`, `datetime`) VALUES ('{$comment->getTicketId()}', '{$comment->getUserId()}', '{$comment->getText()}', '{$comment->getDatetime()}')";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    public function editComment(\Support\Comment $comment)
    {
        $sql = "UPDATE `ticket_comments` SET `ticket_id`='{$comment->getTicketId()}',`uid`='{$comment->getUserId()}',`text`='{$comment->getText()}',`datetime`='{$comment->getDatetime()}' WHERE `id`='{$comment->getId()}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function editCommentMsg(int $id, string $msg)
    {
        $sql = "UPDATE `ticket_comments` SET `text`='{$msg}' WHERE `id`='{$id}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function deleteComment(int $id)
    {
        $sql = "DELETE FROM `ticket_comments` WHERE id='{$id}'";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function getCommentsByTicketId(int $ticket_id)
    {
        $sql = "SELECT * FROM `ticket_comments` WHERE ticket_id = '{$ticket_id}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getCommentById(int $comment_id)
    {
        $sql = "SELECT * FROM `ticket_comments` WHERE id = '{$comment_id}'";
        $results = $this->getDb()->query($sql);
        return $results->row;
    }

    public function getCommentsHeatMap(int $project_id): array
    {
        $sql = "SELECT UNIX_TIMESTAMP(DATE_FORMAT(tc.datetime, '%Y-%m-%d 00:00:00')) AS datetime FROM `tickets` t LEFT JOIN `ticket_comments` tc ON (t.id = tc.ticket_id) WHERE t.project_id = {$project_id}";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    //Dashboard - Latest Comments Routine
    public function getTicketsWithUserComment(int $uid, int $limit)
    {
        $sql = "SELECT t.*, ticket_id, MAX(datetime) as latest FROM `ticket_comments` tc LEFT JOIN `tickets` t ON (tc.ticket_id = t.id) WHERE uid={$uid} GROUP BY ticket_id ORDER BY latest DESC LIMIT {$limit}";
        return $this->getDb()->query($sql)->rows;
    }

    public function getUserLatestCommentDatetimeByTicket(int $ticket_id, int $uid): string //Timestamp with last comment
    {
        $sql = "SELECT MAX(datetime) as latest FROM `ticket_comments` WHERE uid={$uid} AND ticket_id = {$ticket_id}";
        return $this->getDb()->query($sql)->row['latest'];
    }

    public function getUsersCommentsAfter(int $ticket_id, string $timestamp_from)
    {
        $sql = "SELECT * FROM `ticket_comments` WHERE ticket_id = {$ticket_id} AND datetime >= '{$timestamp_from}'";
        return $this->getDb()->query($sql)->rows;
    }

    //Get TimeLine data functions
    public function getTimeLineCreatedTicketsByDay(int $project_id, string $day_timestamp)
    {
        $start = date('Y-m-d 00:00:00', strtotime($day_timestamp));
        $end = date('Y-m-d 23:59:59', strtotime($day_timestamp));
        $sql = "SELECT * FROM `tickets` WHERE (created BETWEEN '{$start}' AND '{$end}') AND project_id = {$project_id}";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getTimeLineStartedTicketsByDay(int $project_id, string $day_timestamp)
    {
        $start = date('Y-m-d 00:00:00', strtotime($day_timestamp));
        $end = date('Y-m-d 23:59:59', strtotime($day_timestamp));
        $sql = "SELECT * FROM `tickets` WHERE (start BETWEEN '{$start}' AND '{$end}') AND project_id = {$project_id}";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getTimeLineFinishedTicketsByDay(int $project_id, string $day_timestamp)
    {
        $start = date('Y-m-d 00:00:00', strtotime($day_timestamp));
        $end = date('Y-m-d 23:59:59', strtotime($day_timestamp));
        $sql = "SELECT * FROM `tickets` WHERE (finish BETWEEN '{$start}' AND '{$end}') AND project_id = {$project_id}";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    public function getTimeLineTicketCommentsByDay(int $project_id, string $day_timestamp)
    {
        $start = date('Y-m-d 00:00:00', strtotime($day_timestamp));
        $end = date('Y-m-d 23:59:59', strtotime($day_timestamp));
        $sql = "SELECT * FROM `ticket_comments` tc LEFT JOIN `tickets` t ON (tc.ticket_id = t.id) WHERE (tc.datetime BETWEEN '{$start}' AND '{$end}') AND t.project_id = {$project_id} ORDER BY tc.datetime ASC";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

}