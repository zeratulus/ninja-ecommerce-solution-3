<?php

class ControllerCommonMyTickets extends \Ninja\AdminController {
	private $error = array();

    public function index()
    {
        $data = array();

        $this->getLanguage()->load('support/tickets');
        //Get my tickets
        $this->getLoader()->model('support/support');

        $data['tickets'] = array();

        $in_progress_total_seconds  = 0;
        $completed_total_seconds  = 0;

        //in progress
        $filter = new \Support\TicketFilter();
        $filter->setDelegatedToUid($this->getUser()->getId());
        $filter->setStatus(\Support\Status::PROGRESS_ID);

        //TODO: Sort by priority?! 0_o
        $progress = $this->model_support_support->getTickets($filter);
        foreach ($progress as $item) {
            $ticket = createTicketFromArray($this->registry, $item);
            $progress_diff = date_diff(new DateTime($ticket->getStart()), new DateTime());
            $progress_diff = dateIntervalToSeconds($progress_diff);
            $in_progress_total_seconds = (int)$in_progress_total_seconds + (int)$progress_diff;
            $data['tickets'][] = $ticket;
        }

        $hs = $this->getLanguage()->get('text_hour_short');

        //new
        $filter->setStatus(\Support\Status::NEW_ID);
        //TODO: Sort by priority?! 0_o
        $new = $this->model_support_support->getTickets($filter);
        foreach ($new as $item) {
            $ticket = createTicketFromArray($this->registry, $item);
            $data['tickets'][] = $ticket;
        }

        //Total statistics
        $in_progress_total_time = round($in_progress_total_seconds / 60 / 60, 0);
        $data['in_progress_total_time'] = $in_progress_total_time . ' ' . $hs;

        //Projects
        $data['projects'] = array();
        if ($this->getUser()->getGroupId() == \Support\User::ADMIN_GROUP_ID) {
            $projects = $this->model_support_support->getProjects();
        } else {
            $projects = $this->model_support_support->getProjectsByPermissions($this->getUser()->getId());
        }

        foreach ($projects as $item) {
            $project = new \Support\Project($this->registry);
            $project->mapData($item);
            $data['projects'][$project->getId()] = $project;
        }

        //Latest Comments After My
        $data['comments'] = array();
        $data['commented_tickets'] = array();
        $tickets = $this->model_support_support->getTicketsWithUserComment($this->getUser()->getId(), 5);
        foreach ($tickets as $ticket_info) {
            $ticket = createTicketFromArray($this->registry, $ticket_info);
            $data['commented_tickets'][$ticket->getId()] = $ticket;
            $comments = $this->model_support_support->getUsersCommentsAfter($ticket->getId(), $ticket_info['latest']);
            foreach ($comments as $comment_info) {
                $comment = new \Support\Comment($this->registry);
                $comment->mapData($comment_info);
                $data['comments'][$ticket->getId()][] = $comment;
            }
        }

        return $this->load->view('common/mytickets', $data);
    }
}