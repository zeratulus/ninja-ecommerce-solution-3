<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 06.10.19
 * Time: 17:14
 */

class ControllerSupportTicket extends \Ninja\AdminController {

    private $error = array();

    public function index()
    {
        $this->getLoader()->language('support/ticket');
        $this->getLoader()->model('support/support');

        $data['user_token'] = $this->getUserToken();
        $data['user_id'] = $this->getUser()->getId();

        $url = '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_projects'),
            'href' => $this->getUrl()->link('support/projects', 'user_token=' . $data['user_token'] . $url)
        );

        if ( (!empty($project_id = $this->getRequest()->issetGet('project_id')))
            && (!empty($ticket_id = $this->getRequest()->issetGet('ticket_id'))) ) {
            $url .= "&project_id={$project_id}";
            $url .= "&ticket_id={$ticket_id}";
            $data['ticket_id'] = $ticket_id;
            $data['uid'] = $this->getUser()->getId();

            $project_info = $this->model_support_support->getProjectById($project_id);
            $project = new Support\Project($this->registry);
            $project->mapData($project_info);
            $data['project'] = $project;

            $data['breadcrumbs'][] = array(
                'text' => $project->getTitle(),
                'href' => $this->getUrl()->link('support/tickets', 'user_token=' . $data['user_token'] . $url)
            );

            $this->processSuccessMessage($data);
            $this->processWarningMessage($data);
            $this->processSelected($data);

            $data['add'] = $this->getUrl()->link('support/ticket/add', "user_token={$data['user_token']}{$url}");
            $data['cancel'] = $this->getUrl()->link('support/tickets', "&project_id={$project_id}&user_token={$data['user_token']}");

            $result = $this->model_support_support->getTicketById($ticket_id);
            $ticket = new \Support\Ticket($this->registry);
            $ticket->mapData($result);
            $this->getDocument()->setTitle($ticket->getTitle());
            $data['ticket'] = $ticket;

            $data['breadcrumbs'][] = array(
                'text' => $ticket->getTitle(),
                'href' => $this->getUrl()->link('support/ticket', "user_token={$data['user_token']}{$url}")
            );

            if ($ticket->getCategoryId() > 0) {
                $category_info = $this->model_support_support->getCategoryById($ticket->getCategoryId());
                $obj = new \Support\Category($this->registry);
                $obj->mapData($category_info);
                $data['category'] = $obj;
            } elseif ($ticket->getCategoryId() == -1) {
                $obj = new \Support\Category($this->registry);
                $obj->setId(-1);
                $obj->setTitle($this->getLanguage()->get('text_no_category'));
                $data['category'] = $obj;
            };

            $data['priorities'] = array(
                \Support\Priority::LOW => $this->getLanguage()->get('priority_low'),
                \Support\Priority::NORMAL => $this->getLanguage()->get('priority_normal'),
                \Support\Priority::HIGH => $this->getLanguage()->get('priority_high'),
                \Support\Priority::NOW => $this->getLanguage()->get('priority_now'),
            );

            $data['comments'] = array();
            $results = $this->model_support_support->getCommentsByTicketId($ticket_id);
            foreach ($results as $result) {
                $comment = new \Support\Comment($this->registry);
                $comment->mapData($result);
                $data['comments'][] = $comment;
            }

            $data['counter'] = 0;
            $data['isOddCurrent'] = 0;

            //get current  user info for commenting
            $user = $ticket->getUserInfo($this->user->getId());
            $data['avatar'] = $user->getAvatar();
            $data['fullname'] = $user->getFullName();

            $data['href_ajax_add_comment'] = $this->getUrl()->link('support/ticket/addComment', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_edit_comment_modal'] = $this->getUrl()->link('support/ticket/editCommentModal', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_delete_comment'] = $this->getUrl()->link('support/ticket/deleteComment', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_change_status'] = $this->getUrl()->link('support/ticket/changeTicketStatus', "user_token={$data['user_token']}{$url}");

            //Get Current logged User Info
            $this->getLoader()->model('user/user');
            $user_info = $this->model_user_user->getUserById($this->getUser()->getId());
            $user = new \Support\User($this->registry);
            $user->mapData($user_info);
            $user->loadPermissionsForProject($project_id);
            $data['user'] = $user;

            $data['header'] = $this->getLoader()->controller('common/header');
            $data['column_left'] = $this->getLoader()->controller('common/column_left');
            $data['footer'] = $this->getLoader()->controller('common/footer');

            $this->getResponse()->setOutput($this->getLoader()->view('support/ticket', $data));
        } else {
            $this->getSession()->data['warning'] = $this->getLanguage()->get('error_no_project_id');
            $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$data['user_token']}"));
        }

    }

    public function addComment()
    {
        $insert_id = -1;
        $isError = true;
        $json = array();

        if ($this->getRequest()->server['REQUEST_METHOD'] == 'POST') {
            $ticket_id = $this->getRequest()->issetPost('ticket_id');
            $text = $this->getRequest()->issetPost('text');
            $uid = $this->getRequest()->issetPost('uid');
            if (!empty($ticket_id) && !empty($text) && !empty($uid)) {
                $this->getLoader()->model('support/support');
                $this->getRequest()->post['datetime'] = nowMySQLTimestamp();
                $this->getRequest()->post['id'] = -1;
                $comment = new \Support\Comment($this->registry);
                $comment->mapData($this->getRequest()->post);
                if ($insert_id = $this->model_support_support->addComment($comment)) {
                    $isError = false;
                }
            }
        }

        if ($isError) {
            $json['error'] = true;
        } else {
            $json['success'] = true;
        }

        $json['request'] = array(
            'comment_id' => $insert_id,
            'status' => $this->getRequest()->post
        );

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));

    }

    protected function validateForm()
    {
        if (!$this->getUser()->hasPermission('modify', 'support/tickets')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return empty($this->error);
    }

    public function editCommentModal()
    {
        $json = array();
        $data = array();
        $errors = array();

        if (($this->getRequest()->isRequestMethodPost()) &&
            (!empty($comment_id = $this->getRequest()->issetPost('comment_id')))
        ) {
            $this->getLanguage()->load('support/ticket');
            $this->getLoader()->model('support/support');
            $result = $this->model_support_support->getCommentById($comment_id);
            $comment = new \Support\Comment($this->registry);
            $comment->mapData($result);
            $data['comment'] = $comment;
            $data['uid'] = $this->getUser()->getId();

            if ($this->getUser()->getId() != $comment->getUserId()) {
                $errors[] = $this->getLanguage()->get('error_comment_user_not_equal');
            } else {
                $data['href_ajax_edit_comment'] = $this->getUrl()->link('support/ticket/editComment', "user_token={$this->getSession()->data['user_token']}");
                $json['modal'] = $this->getLoader()->view('support/modal_edit_comment', $data);
                $json['success'] = true;
            }
        } else {
            $errors[] = $this->getLanguage()->get('error_get_comment_modal');
        }

        if (!empty($errors)) {
            $json['error'] = true;
            $json['errors'] = $errors;
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function editComment()
    {
        $json = array();

        if ( ($this->getRequest()->isRequestMethodPost()) &&
            (!empty($comment_id = $this->getRequest()->issetPost('comment_id'))) &&
            (!empty($text = $this->getRequest()->issetPost('text'))) ) {
            $this->getLoader()->model('support/support');

            $result = $this->model_support_support->editCommentMsg($comment_id, $text);
            if ($result) {
                $json['success'] = true;
            }
        } else {
            $json['error'] = true;
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function deleteComment()
    {
        $json = array();

        if (($this->getRequest()->isRequestMethodPost()) &&
            (!empty($comment_id = $this->getRequest()->issetPost('comment_id'))) &&
            (!empty($uid = $this->getRequest()->issetPost('uid')))
        ) {
            $this->getLoader()->model('support/support');

            $result = $this->model_support_support->deleteComment($comment_id);
            if ($result) {
                $json['success'] = true;
            }

        } else {
            $json['error'] = true;
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function changeTicketStatus()
    {
        $app = new \Tools\Monsters\ApplicationJson($this->registry);

        $json = array();

        if (($app->getRequest()->isRequestMethodPost()) &&
            (!empty($ticket_id = $app->getRequest()->issetPost('ticket_id'))) &&
            (!empty($status = $app->getRequest()->issetPost('status')))
        ) {
            $this->getLoader()->model('support/support');

            $result = $this->model_support_support->changeTicketStatus($ticket_id, $status);
            if ($result) {
                $json['success'] = true;
            }
        } else {
            $json['error'] = true;
        }

        $app->setOutput($json);
    }

}