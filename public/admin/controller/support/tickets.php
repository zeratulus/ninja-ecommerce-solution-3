<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 16.09.19
 * Time: 22:44
 */

class ControllerSupportTickets extends \Ninja\AdminController
{

    private $error = array();

    private function fillHeatMap(&$data, $key)
    {
        $key = "'" . $key . "'";
        if (($key != "''") && ($key != "'0'")) {
            if (isset($data['heatmap'][$key])) {
                $data['heatmap'][$key] = (int)$data['heatmap'][$key] + 1;
            } else {
                $data['heatmap'][$key] = 1;
            }
        }
    }

    public function index()
    {
        $this->getLoader()->language('support/tickets');
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model('support/support');

        $this->getDocument()->addStyle('view/javascript/charts/frappe-charts.min.css', 'stylesheet');
        $this->getDocument()->addScript('view/javascript/charts/frappe-charts.min.iife.js');

        $this->getDocument()->addStyle('view/javascript/gantt/frappe-gantt.css', 'stylesheet');
        $this->getDocument()->addScript('view/javascript/gantt/frappe-gantt.js');

        $data['user_token'] = $this->getUserToken();

        $data['route'] = 'support/tickets';
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

        if (!empty($project_id = $this->getRequest()->issetGet('project_id'))) {
            $data['project_id'] = $project_id;
            $url .= "&project_id={$project_id}";

            $project_info = $this->model_support_support->getProjectById($project_id);
            $project = new Support\Project($this->registry);
            $project->mapData($project_info);
            $data['project'] = $project;
            if ($project->getTimezone()) {
                $res = date_default_timezone_set($project->getTimezone());
                $this->getDebugBar()->getMessages()->addMessage($res);
            }
    
            $data['breadcrumbs'][] = array(
                'text' => $project->getTitle(),
                'href' => $this->getUrl()->link('support/tickets', "{$url}&user_token={$data['user_token']}")
            );

            $this->processSelected($data);
            $this->processWarningMessage($data);
            $this->processSuccessMessage($data);

            $data['add'] = $this->getUrl()->link('support/tickets/add', "user_token={$data['user_token']}{$url}");
            $data['delete'] = $this->getUrl()->link('support/tickets/delete', "user_token={$data['user_token']}{$url}");

            $data['categories'] = array();
            $categories = $this->model_support_support->getCategoriesByProjectId($project_id);
            foreach ($categories as $category) {
                $obj = new \Support\Category($this->registry);
                $obj->mapData($category);
                $data['categories'][] = $obj;
            }

            $filter = new Support\TicketFilter();
            $filter->setProjectId($project_id);

            if (!empty($filter_user_created = $this->getRequest()->issetGet('filter_user_created'))) {
                $filter->setCreatedByUid($filter_user_created);
                $data['filter_user_created'] = $filter_user_created;
            } else {
                $data['filter_user_created'] = -1;
            }

            if (!empty($filter_user_delegated = $this->getRequest()->issetGet('filter_user_delegated'))) {
                $filter->setDelegatedToUid($filter_user_delegated);
                $data['filter_user_delegated'] = $filter_user_delegated;
            } else {
                $data['filter_user_delegated'] = -1;
            }

            if (!empty($filter_status = $this->getRequest()->issetGet('filter_status'))) {
                $filter->setStatus($filter_status);
                $data['filter_status'] = $filter_status;
            } else {
                $filter->setStatus(\Support\Status::FILTER_ALL);
                $data['filter_status'] = 0;
            }

            if (!empty($filter_category = $this->getRequest()->issetGet('filter_category'))) {
                $filter->setCategoryId($filter_category);
                $data['filter_category'] = $filter_category;
            } else {
                $data['filter_category'] = -1;
            }

            if (!empty($filter_name = $this->getRequest()->issetGet('filter_name'))) {
                $filter->setTitle($filter_name);
                $data['filter_name'] = $filter_name;
            } else {
                $data['filter_name'] = '';
            }

            if (!empty($page = $this->getRequest()->issetGet('page'))) {
                $filter->setPage($page);
                $data['page'] = $page;
            } else {
                $data['page'] = 1;
            }

            if (!empty($filter_limit = $this->getRequest()->issetGet('filter_limit'))) {
                $filter->setLimit($filter_limit);
                $data['filter_limit'] = $filter_limit;
                $url .= '&filter_limit=' . $filter_limit;
            } else {
                $data['filter_limit'] = '';
            }

            if (!empty($is_one_page = $this->getRequest()->issetGet('filter_as_one_page'))) {
                $filter->setIsOnePage($is_one_page);
                $data['filter_as_one_page'] = $is_one_page;
            } else {
                $data['filter_as_one_page'] = false;
            }

            if (!empty($sort_field = $this->getRequest()->issetGet('sort'))) {
                $filter->setSortField($sort_field);
                $data['sort'] = $sort_field;
            } else {
                $data['sort'] = 'id';
            }

            if (!empty($sort_dir = $this->getRequest()->issetGet('sort_dir'))) {
                $filter->setSortDirection($sort_dir);
                $data['sort_dir'] = $sort_dir;
            } else {
                $data['sort_dir'] = 'ASC';
            }

            //get sort icon
            if ($data['sort_dir'] == 'ASC') {
                $data['sort_icon'] = '<i class="fa fa-sort-alpha-down"></i>';
                $data['sort_dir_next'] = '&sort_dir=DESC';
            } else {
                $data['sort_icon'] = '<i class="fa fa-sort-alpha-up"></i>';
                $data['sort_dir_next'] = '&sort_dir=ASC';
            }

            $data['sort_id'] = $this->getUrl()->link('support/tickets', "user_token={$data['user_token']}{$url}&sort=id");
            $data['sort_title'] = $this->getUrl()->link('support/tickets', "user_token={$data['user_token']}{$url}&sort=title");
            $data['sort_status'] = $this->getUrl()->link('support/tickets', "user_token={$data['user_token']}{$url}&sort=status");
            $data['sort_category_id'] = $this->getUrl()->link('support/tickets', "user_token={$data['user_token']}{$url}&sort=category_id");

            $ds = $this->getLanguage()->get('text_day_short');
            $hs = $this->getLanguage()->get('text_hour_short');
            $ms = $this->getLanguage()->get('text_min_short');

            $in_progress_total_seconds = 0;
            $completed_total_seconds = 0;
            $data['childs'] = array();
            $data['tickets'] = array();
            $results = $this->model_support_support->getTickets($filter);
            foreach ($results as $row) {
                $ticket = new \Support\Ticket($this->registry);
                $ticket->mapData($row);
                if ($ticket->getParentTaskId() >= 1) {
                    $data['childs'][$ticket->getParentTaskId()][] = $ticket;
                } else {
                    $data['tickets'][$ticket->getId()] = $ticket;
                }

                if ($ticket->getStatus() == \Support\Status::PROGRESS_ID) {
                    $progress_diff = date_diff(new DateTime($ticket->getStart()), new DateTime());
                    $progress_diff = dateIntervalToSeconds($progress_diff);
                    $in_progress_total_seconds = (int)$in_progress_total_seconds + (int)$progress_diff;
                } elseif ($ticket->getStatus() == \Support\Status::COMPLETED_ID) {
                    $completed_diff = date_diff(new DateTime($ticket->getStart()), new DateTime());
                    $completed_diff = dateIntervalToSeconds($completed_diff);
                    $completed_total_seconds = (int)$completed_total_seconds + (int)$completed_diff;
                }

            }

            //Total statistics
            $in_progress_total_time = round($in_progress_total_seconds / 60 / 60, 0);
            $competed_total_time = round($completed_total_seconds / 60 / 60, 0);

            $data['in_progress_total_time'] = $in_progress_total_time . ' ' . $hs;
            $data['competed_total_time'] = $competed_total_time . ' ' . $hs;

            //Heatmap
            $data['heatmap'] = array();
            $results = $this->model_support_support->getTicketsHeatMap($project_id);
            foreach ($results as $result) {
                $this->fillHeatMap($data, $result['created']);
                $this->fillHeatMap($data, $result['start']);
                $this->fillHeatMap($data, $result['finish']);
            }
            $results = $this->model_support_support->getCommentsHeatMap($project_id);
            foreach ($results as $result) {
                $this->fillHeatMap($data, $result['datetime']);
            }

            $data['year'] = date('Y');

            //Heatmap localisation routine
            $data['heatmap_months'] = json_encode(array(
                "JAN" => $this->getLanguage()->get('text_january_short'),
                "FEB" => $this->getLanguage()->get('text_february_short'),
                "MAR" => $this->getLanguage()->get('text_march_short'),
                "APR" => $this->getLanguage()->get('text_april_short'),
                "MAY" => $this->getLanguage()->get('text_may_short'),
                "JUN" => $this->getLanguage()->get('text_june_short'),
                "JUL" => $this->getLanguage()->get('text_july_short'),
                "AUG" => $this->getLanguage()->get('text_august_short'),
                "SEP" => $this->getLanguage()->get('text_september_short'),
                "OCT" => $this->getLanguage()->get('text_october_short'),
                "NOV" => $this->getLanguage()->get('text_november_short'),
                "DEC" => $this->getLanguage()->get('text_december_short'),
            ));

            $data['heatmap_days'] = json_encode(array(
                "Sun" => $this->getLanguage()->get('text_sunday_short'),
                "Mon" => $this->getLanguage()->get('text_monday_short'),
                "Tue" => $this->getLanguage()->get('text_tuesday_short'),
                "Wed" => $this->getLanguage()->get('text_wednesday_short'),
                "Thu" => $this->getLanguage()->get('text_thursday_short'),
                "Fri" => $this->getLanguage()->get('text_friday_short'),
                "Sat" => $this->getLanguage()->get('text_saturday_short'),
            ));

            $data['tickets_total'] = $this->model_support_support->getTicketsTotal($filter);

            $data['setting'] = $this->getUrl()->link("support/tickets/setting", "user_token={$data['user_token']}{$url}", true);

            //Get Current User Info
            $this->getLoader()->model('user/user');
            $user_info = $this->model_user_user->getUserById($this->user->getId());
            $user = new \Support\User($this->registry);
            $user->mapData($user_info);
            $user->loadPermissionsForProject($project_id);
            $data['user'] = $user;

            //Get Users List For Filters
            $data['users'] = array();
            $users_filter = array(
                'filters' => array(
                    'status' => true
                )
            );
            $users = $this->model_user_user->getUsers($users_filter);
            foreach ($users as $user_info) {
                if ($user_info['user_id'] != $user->getId()) {
                    $user = new \Support\User($this->registry);
                    $user->mapData($user_info);
                    $data['users'][] = $user;
                }
            }

            $data['statuses'] = $this->model_support_support->getTicketStatuses();

            $data['categories'] = array();
            $categories = $this->model_support_support->getCategoriesByProjectId($project_id);
            foreach ($categories as $category) {
                $obj = new \Support\Category($this->registry);
                $obj->mapData($category);
                $data['categories'][] = $obj;
            }

            //if filter all as one page we do not need a pagination
            if (!$is_one_page) {
                $pagination = new Pagination();
                $pagination->total = $data['tickets_total'];
                $pagination->page = $page;
                $pagination->limit = $this->getConfig()->get('config_limit_admin');
                $pagination->url = $this->getUrl()->link('support/tickets', 'user_token=' . $this->getUserToken() . $url . '&page={page}');

                $data['pagination'] = $pagination->render();
            }

            //$data['results'] = sprintf($this->language->get('text_pagination'), ($data['tickets_total']) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($data['tickets_total'] - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $data['tickets_total'], ceil($data['tickets_total'] / $this->config->get('config_limit_admin')));

            $data['filter_action'] = $this->getUrl()->link('support/tickets');
            $data['delete'] = $this->getUrl()->link('support/tickets/delete', "&user_token={$this->getUserToken()}{$url}&project_id={$project_id}");
            $data['href_ajax_gant_diagram'] = $this->getUrl()->link('support/tickets/gantModal', "&user_token={$this->getUserToken()}{$url}");
            $data['href_ajax_timeline'] = $this->getUrl()->link('support/tickets/timelineModal', "&user_token={$this->getUserToken()}{$url}");

            $data['header'] = $this->getLoader()->controller('common/header');
            $data['column_left'] = $this->getLoader()->controller('common/column_left');
            $data['footer'] = $this->getLoader()->controller('common/footer');

            $this->getResponse()->setOutput($this->getLoader()->view('support/tickets', $data));
        } else {
            $this->getSession()->data['warning'] = $this->getLanguage()->get('error_no_project_id');
            $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$data['user_token']}"));
        }

    }

    public function add()
    {
        $this->getLoader()->language('support/tickets');
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model('support/support');

        if (($this->getRequest()->isRequestMethodPost()) && ($this->validateForm())) {
            if (isset($this->getRequest()->get['project_id'])) {
                $ticket = new \Support\Ticket($this->registry);
                $ticket->mapData($this->getRequest()->post);
                $result = $this->model_support_support->addTicket($ticket);
                if ($result) {
                    //Send mail to delegated user
                    $logo = $this->getConfig()->get('config_logo');
                    $heading_title = $this->getLanguage()->get('text_new_task');

                    $message_body = $ticket->getDescriptionHtml();

                    $message_foot = '2007 - ' . date('Y') . ' - ' . $this->getConfig()->get('config_name');

                    $mail_data = array(
                        'title' => $this->getConfig()->get('config_name'),
                        'logo' => HTTPS_CATALOG . 'image/' . $logo,
                        'heading_title' => $heading_title,
                        'message_body' => $message_body,
                        'message_foot' => $message_foot,
                    );

                    $this->getLoader()->model('user/user');
                    $delegated_user_info = $this->model_user_user->getUserById($ticket->getDelegatedToUid());
                    $creator_info = $this->model_user_user->getUserById($ticket->getCreatedByUid());

                    $mail = new \Support\Mail($this->registry);
                    $mail->getMail()->setFrom($creator_info['email']);
                    $mail->getMail()->setTo($delegated_user_info['email']);
                    $mail->getMail()->setSender("{$creator_info['firstname']} {$creator_info['lastname']} <{$creator_info['email']}>");
                    $mail->getMail()->setSubject($this->getLanguage()->get('text_new_task') . ': #' . $result . ' ' . $ticket->getTitle());
                    $mail->getMail()->setHtml($this->getLoader()->view('support/mails/default', $mail_data));
                    $mail->send();

                    $this->getSession()->data['success'] = $this->getLanguage()->get('text_success_add');
                    $this->getResponse()->redirect($this->getUrl()->link('support/tickets', "user_token={$this->getSession()->data['user_token']}&project_id={$this->getRequest()->get['project_id']}"));
                }
            }
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->getLoader()->language('support/tickets');
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model('support/support');

        if (($this->getRequest()->isRequestMethodPost()) && ($this->validateForm())) {
            if ((isset($this->getRequest()->get['project_id'])) && (isset($this->getRequest()->get['ticket_id']))) {
                $ticket = new \Support\Ticket($this->registry);
                $ticket->mapData($this->getRequest()->post);
                $old_info = $this->model_support_support->getTicketById($ticket->getId());
                $old_ticket = new \Support\Ticket($this->registry);
                $old_ticket->mapData($old_info);

                //Send mail to new delegated user if changed
                if ($old_ticket->getDelegatedToUid() != $ticket->getDelegatedToUid()) {
                    $logo = $this->getConfig()->get('config_logo');
                    $heading_title = $this->getLanguage()->get('text_mail_redelegated');

                    $message_body = $ticket->getDescriptionHtml();

                    $message_foot = '2007 - ' . date('Y') . ' - ' . $this->getConfig()->get('config_name');

                    $mail_data = array(
                        'title' => $this->getConfig()->get('config_name'),
                        'logo' => HTTPS_CATALOG . 'image/' . $logo,
                        'heading_title' => $heading_title,
                        'message_body' => $message_body,
                        'message_foot' => $message_foot,
                    );

                    $this->getLoader()->model('user/user');
                    $delegated_user_info = $this->model_user_user->getUserById($ticket->getDelegatedToUid());
                    $creator_info = $this->model_user_user->getUserById($ticket->getCreatedByUid());

                    $mail = new \Support\Mail($this->registry);
                    $mail->getMail()->setFrom($creator_info['email']);
                    $mail->getMail()->setTo($delegated_user_info['email']);
                    $mail->getMail()->setSender("{$creator_info['firstname']} {$creator_info['lastname']} <{$creator_info['email']}>");
                    $mail->getMail()->setSubject($this->getLanguage()->get('text_mail_redelegated') . ': #' . $this->getRequest()->get['ticket_id'] . ' ' . $ticket->getTitle());
                    $mail->getMail()->setHtml($this->getLoader()->view('support/mails/default', $mail_data));
                }

                $result = $this->model_support_support->editTicket($ticket);
                if ($result) {
                    if ($old_ticket->getDelegatedToUid() != $ticket->getDelegatedToUid()) {
                        $mail->send();
                    }

                    $this->getSession()->data['success'] = $this->getLanguage()->get('text_success_edit');
                    $this->getResponse()->redirect($this->getUrl()->link('support/tickets', "user_token={$this->getSession()->data['user_token']}&project_id={$this->getRequest()->get['project_id']}"));
                }
            }
        }

        $this->getForm();
    }

    public function delete()
    {
        if (($this->getRequest()->isRequestMethodPost())
            && (!empty($selected = (array)$this->getRequest()->issetPost('selected')))
            && ($this->validateDelete())) {

            $this->getLoader()->model('support/support');
            foreach ($selected as $id) {
                $this->model_support_support->removeTicket($id);
            }
        }
        $this->getResponse()->redirect($this->getUrl()->link('support/tickets', "user_token={$this->getUserToken()}&project_id={$this->getRequest()->get['project_id']}", true));
    }

    protected function getForm()
    {
        $data['user_token'] = $this->getUserToken();
        $isAdd = !isset($this->getRequest()->get['ticket_id']);
        $data['isAdd'] = $isAdd;
        if (!empty($project_id = $this->getRequest()->issetGet('project_id'))) {
            $data['text_form'] = $isAdd ? $this->getLanguage()->get('text_add') : $this->getLanguage()->get('text_edit');

            $this->getDocument()->setTitle($data['text_form']);
            $this->getLoader()->model('support/support');
            $this->getLoader()->model('user/user');

            $url = '';
            if (isset($this->getRequest()->get['sort'])) {
                $url .= '&sort=' . $this->getRequest()->get['sort'];
            }

            if (isset($this->getRequest()->get['order'])) {
                $url .= '&order=' . $this->getRequest()->get['order'];
            }

            if (isset($this->getRequest()->get['page'])) {
                $url .= '&page=' . $this->getRequest()->get['page'];
            }

            if (isset($this->getRequest()->get['project_id'])) {
                $url .= '&project_id=' . $this->getRequest()->get['project_id'];
            }

            $data['breadcrumbs'] = array();

            $route_args = "user_token={$data['user_token']}";
            $data['breadcrumbs'][] = array(
                'text' => $this->getLanguage()->get('text_home'),
                'href' => $this->getUrl()->link('common/dashboard', $route_args)
            );

            if (!empty($this->getRequest()->get['project_id'])) {
                $route_args .= "&project_id={$project_id}";
            }
            $data['breadcrumbs'][] = array(
                'text' => $this->getLanguage()->get('text_projects'),
                'href' => $this->getUrl()->link('support/projects', $route_args)
            );

            $project_info = $this->model_support_support->getProjectById($project_id);
            $project = new \Support\Project($this->registry);
            $project->mapData($project_info);

            $data['breadcrumbs'][] = array(
                'text' => $project->getTitle(),
                'href' => $this->getUrl()->link('support/tickets', $route_args, true)
            );

            if ($isAdd) {
                $data['action'] = $this->getUrl()->link('support/tickets/add', "user_token={$data['user_token']}{$url}");
            } else {
                if (isset($this->getRequest()->get['ticket_id'])) $url .= '&ticket_id=' . $this->getRequest()->get['ticket_id'];
                $data['action'] = $this->getUrl()->link('support/tickets/edit', "user_token={$data['user_token']}{$url}");
            }

            $data['breadcrumbs'][] = array(
                'text' => $data['text_form'],
                'href' => $data['action']
            );

            $this->processWarningMessage($data);

            $data['cancel'] = $this->getUrl()->link('support/tickets', "user_token={$data['user_token']}{$url}");

            //get info if edit
            if ((!$isAdd) && (!$this->getRequest()->isRequestMethodPost())) {
                $ticket_info = $this->model_support_support->getTicketById($this->getRequest()->get['ticket_id']);
            }

            //Prepare Form Data
            if (isset($this->getRequest()->post['title'])) {
                $data['title'] = $this->getRequest()->post['title'];
            } elseif (!$isAdd) {
                $data['title'] = $ticket_info['title'];
            } else {
                $data['title'] = '';
            }

            if (isset($this->getRequest()->post['description'])) {
                $data['description'] = $this->getRequest()->post['description'];
            } elseif (!$isAdd) {
                $data['description'] = $ticket_info['description'];
            } else {
                $data['description'] = '';
            }


            if (isset($this->getRequest()->post['start'])) {
                $data['start'] = $this->getRequest()->post['start'];
            } elseif (!$isAdd) {
                $data['start'] = $ticket_info['start'];
            } else {
                $data['start'] = nowMySQLTimestamp();
            }

            if (isset($this->getRequest()->post['finish'])) {
                $data['finish'] = $this->getRequest()->post['finish'];
            } elseif (!$isAdd) {
                $data['finish'] = $ticket_info['finish'];
            } else {
                $data['finish'] = '';
            }

            if (isset($this->getRequest()->post['status'])) {
                $data['status'] = $this->getRequest()->post['status'];
            } elseif (!$isAdd) {
                $data['status'] = $ticket_info['status'];
            } else {
                $data['status'] = 1; //new
            }

            if (isset($this->getRequest()->post['created_by_uid'])) {
                $data['created_by_uid'] = $this->getRequest()->post['created_by_uid'];
            } elseif (!$isAdd) {
                $data['created_by_uid'] = $ticket_info['created_by_uid'];
            } else {
                $data['created_by_uid'] = $this->user->getId();
            }

            if (isset($this->getRequest()->post['delegated_to_uid'])) {
                $data['delegated_to_uid'] = $this->getRequest()->post['delegated_to_uid'];
            } elseif (!$isAdd) {
                $data['delegated_to_uid'] = $ticket_info['delegated_to_uid'];
            } else {
                $data['delegated_to_uid'] = '';
            }

            if (isset($this->getRequest()->post['parent_task_id'])) {
                $data['parent_task_id'] = $this->getRequest()->post['parent_task_id'];
            } elseif (!$isAdd) {
                $data['parent_task_id'] = $ticket_info['parent_task_id'];
            } else {
                $data['parent_task_id'] = '';
            }

            if (isset($this->getRequest()->post['category_id'])) {
                $data['category_id'] = $this->getRequest()->post['category_id'];
            } elseif (!$isAdd) {
                $data['category_id'] = $ticket_info['category_id'];
            } else {
                $data['category_id'] = '';
            }

            if (isset($this->getRequest()->post['priority'])) {
                $data['priority'] = $this->getRequest()->post['priority'];
            } elseif (!$isAdd) {
                $data['priority'] = $ticket_info['priority'];
            } else {
                $data['priority'] = \Support\Priority::NORMAL;
            }

            if (isset($this->getRequest()->post['deadline'])) {
                $data['deadline'] = $this->getRequest()->post['deadline'];
            } elseif (!$isAdd) {
                $data['deadline'] = $ticket_info['deadline'];
            } else {
                $data['deadline'] = '';
            }

            if (isset($this->getRequest()->post['created'])) {
                $data['created'] = $this->getRequest()->post['created'];
            } elseif (!$isAdd) {
                $data['created'] = $ticket_info['created'];
            } else {
                $data['created'] = nowMySQLTimestamp();
            }

            $data['statuses'] = $this->model_support_support->getTicketStatuses();

            $data['categories'] = array();
            $categories = $this->model_support_support->getCategoriesByProjectId($project_id);
            foreach ($categories as $category) {
                $obj = new \Support\Category($this->registry);
                $obj->mapData($category);
                $data['categories'][] = $obj;
            }

            $data['users'] = array();
            $users_filter = array(
                'filters' => array(
                    'status' => true
                )
            );
            $users = $this->model_user_user->getUsers($users_filter);
            foreach ($users as $user_info) {
                $user = new \Support\User($this->registry);
                $user->mapData($user_info);
                $data['users'][] = $user;
            }

            $data['priorities'] = array(
                0 => $this->getLanguage()->get('priority_low'),
                1 => $this->getLanguage()->get('priority_normal'),
                2 => $this->getLanguage()->get('priority_high'),
                3 => $this->getLanguage()->get('priority_now'),
            );

            $data['tickets'] = array();
            $ticket_filter = new Support\TicketFilter();
            $ticket_filter->setProjectId($project_id);
            $ticket_filter->setStatus(\Support\Status::FILTER_ALL);
            $tickets = $this->model_support_support->getTickets($ticket_filter);
            foreach ($tickets as $ticket) {
                $obj = new Support\Ticket($this->registry);
                $obj->mapData($ticket);
                $data['tickets'][] = $obj;
            }

            $data['header'] = $this->getLoader()->controller('common/header');
            $data['column_left'] = $this->getLoader()->controller('common/column_left');
            $data['footer'] = $this->getLoader()->controller('common/footer');

            $this->getResponse()->setOutput($this->getLoader()->view('support/ticket_form', $data));
        } else {
            $this->getSession()->data['warning'] = $this->getLanguage()->get('error_no_project_id');
            $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$data['user_token']}"));
        }

    }

    protected function validateForm()
    {
        if (!$this->getUser()->hasPermission('modify', 'support/tickets')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        //TODO: Validation
        if (empty($this->getRequest()->post['title'])) {
        }

        if (empty($this->getRequest()->post['description'])) {
        }

        if (empty($this->getRequest()->post['category_id'])) {
            $this->getRequest()->post['category_id'] = -1;
        }

        $this->getRequest()->post['id'] = $this->getRequest()->issetGet('ticket_id');
        //fix for new ticket
        if (empty($this->getRequest()->post['id'])) {
            $this->getRequest()->post['id'] = -1;
        }
        $this->getRequest()->post['project_id'] = $this->getRequest()->issetGet('project_id');
        if (empty($parent_task_id = $this->getRequest()->issetPost('parent_task_id'))) {
            $this->getRequest()->post['parent_task_id'] = -1;
        }

        return empty($this->error);
    }


    public function setting()
    {
        $data['user_token'] = $this->getUserToken();
        $this->getLoader()->language('support/setting');
        $this->getLoader()->model('support/support');

        $url = '';
        if ($project_id = $this->getRequest()->issetGet('project_id')) {
            $url .= '&project_id=' . $project_id;
        }

        if (!empty($project_id)) {
            if ($this->getRequest()->isRequestMethodPost() && $this->validateSetting()) {
                //save permissions...
                if (!empty($permissions = $this->getRequest()->issetPost('users'))) {
                    foreach ($permissions as $user_id => $rights) {
                        $old = $this->model_support_support->getUserPermissionsByProject($project_id, $user_id);
                        $permissions_str = generatePermission($rights['read'], $rights['write'], $rights['create'], $rights['delete']);
//                        $permissions_str = generatePermissionFromArray($rights);
                        if (!empty($old)) {
                            //edit
                            $this->model_support_support->setProjectUserPermission($project_id, $user_id, $permissions_str);
                        } else {
                            //add
                            $this->model_support_support->addUserToProject($project_id, $user_id, $permissions_str);
                        }
                    }
                }
            }

            $data['project_id'] = $project_id;
            $data['text_form'] = $this->language->get('heading_title');

            if (isset($this->getRequest()->get['sort'])) {
                $url .= '&sort=' . $this->getRequest()->get['sort'];
            }
            if (isset($this->getRequest()->get['order'])) {
                $url .= '&order=' . $this->getRequest()->get['order'];
            }
            if (isset($this->getRequest()->get['page'])) {
                $url .= '&page=' . $this->getRequest()->get['page'];
            }

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->getLanguage()->get('text_home'),
                'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $data['user_token'])
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->getLanguage()->get('text_projects'),
                'href' => $this->getUrl()->link('support/projects', 'user_token=' . $data['user_token'])
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->getLanguage()->get('text_tickets'),
                'href' => $this->getUrl()->link('support/tickets', 'user_token=' . $data['user_token'] . $url)
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->getLanguage()->get('text_setting'),
                'href' => $this->getUrl()->link('support/tickets/setting', 'user_token=' . $data['user_token'] . $url)
            );

            $this->processWarningMessage($data);

            $data['project_info'] = $this->model_support_support->getProjectById($project_id);

            $data['colors'] = \Support\Project::$colorsList;

//            $data['statuses'] = array();
//            $statuses = $this->model_support_support->getTicketStatuses($project_id);
//            foreach ($statuses as $status) {
//                $obj = new \Support\Status($this->registry);
//                $obj->mapData($status);
//                $data['statuses'][] = $obj;
//            }

            $data['statuses'] = $this->model_support_support->getTicketStatuses();

            $data['categories'] = array();
            $categories = $this->model_support_support->getCategoriesByProjectId($project_id);
            foreach ($categories as $category) {
                $obj = new \Support\Category($this->registry);
                $obj->mapData($category);
                $data['categories'][] = $obj;
            }

            $data['permissions'] = $this->model_support_support->getProjectUsers($project_id);

            $this->getLoader()->model('user/user');

            $data['users'] = array();
            foreach ($data['permissions'] as $permission) {
                $user_info = $this->model_user_user->getUserById($permission['uid']);
                $user = new \Support\User($this->registry);
                $user->mapData($user_info);
                $user->permissions->setupPermissions($permission);
                $data['users'][] = $user;
            }

            $data['action'] = $this->getUrl()->link('support/tickets/setting', "user_token={$data['user_token']}{$url}");
            $data['cancel'] = $this->getUrl()->link('support/tickets', "user_token={$data['user_token']}{$url}");

            $data['href_ajax_category_delete'] = $this->getUrl()->link('support/tickets/deleteCategory', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_category_add'] = $this->getUrl()->link('support/tickets/addCategory', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_status_add'] = $this->getUrl()->link('support/tickets/addStatus', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_autocomplete_user'] = $this->getUrl()->link('support/tickets/autocompleteUser', "user_token={$data['user_token']}{$url}");
            $data['href_ajax_delete_user'] = $this->getUrl()->link('support/tickets/deleteUser', "user_token={$data['user_token']}{$url}");

            $data['header'] = $this->getLoader()->controller('common/header');
            $data['footer'] = $this->getLoader()->controller('common/footer');

            $this->getResponse()->setOutput($this->getLoader()->view('support/setting', $data));
        } else {
            $this->getSession()->data['warning'] = $this->getLanguage()->get('error_no_project_id');
            $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$data['user_token']}"));
        }
    }

    protected function validateSetting()
    {
        if (!$this->getUser()->hasPermission('modify', 'support/tickets')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return empty($this->error);
    }

    protected function validateDelete()
    {
        if (!$this->getUser()->hasPermission('modify', 'support/tickets')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        return empty($this->error);
    }

    public function addCategory()
    {
        $json = array();

        $isError = true;
        if (($this->getRequest()->isRequestMethodPost()) && ($this->validateSetting())) {
            $title = $this->getRequest()->issetPost('title');
            $description = $this->getRequest()->issetPost('description');
            $project_id = $this->getRequest()->issetPost('project_id');
            if ((!empty($title)) && (!empty($project_id))) {
                $this->getLoader()->model('support/support');

                $category = new Support\Category($this->registry);
                $category->setTitle($title);
                $category->setDescription($description);
                $category->setProjectId($project_id);

                $id = $this->model_support_support->addCategory($category);
                if ($id > 0) $isError = false;
            }
        }

        if (!$isError) {
            $json['success'] = true;
            $json['id'] = $id;
        } else {
            $json['error'] = "Error: Database error!";
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function deleteCategory()
    {
        $json = array();

        $deleted = array();

        $isError = true;
        if (($this->getRequest()->isRequestMethodPost())
            && (!empty($categories = (array)$this->getRequest()->post['ids']))
            && ($this->validateSetting())) {

            $this->getLoader()->model('support/support');

            foreach ($categories as $category_id) {
                $result = $this->model_support_support->removeCategory($category_id);
                if ($result > 0) {
                    $isError = false;
                    $deleted[] = $category_id;
                }
            }

        }

        if (!$isError) {
            $json['success'] = true;
            $json['deleted'] = $deleted;
        } else {
            $json['error'] = "Database Error!";
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function addStatus()
    {
        $json = array();

        $isError = true;
        if (($this->getRequest()->isRequestMethodPost()) && ($this->validateSetting())) {
            $title = $this->getRequest()->issetPost('title');
            $color = $this->getRequest()->issetPost('color');
            $project_id = $this->getRequest()->issetPost('project_id');
            if ((!empty($title)) && (!empty($project_id))) {
                if (empty($color)) $color = 0;

                $this->getLoader()->model('support/support');

                $status = new Support\Status($this->registry);
                $status->setTitle($title);
                $status->setColor($color);
                $status->setProjectId($project_id);

                $id = $this->model_support_support->addTicketStatus($status);
                if ($id > 0) $isError = false;
            }
        }

        if (!$isError) {
            $json['success'] = true;
            $json['id'] = $id;
        } else {
            $json['error'] = "Error: Database error!";
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function gantModal()
    {
        $app = new \Tools\Monsters\ApplicationJson($this->registry);

        $json = array();
        $data = array();
        $errors = array();

        if (($app->getRequest()->isRequestMethodPost()) && ($project_id = $this->getRequest()->issetGet('project_id'))) {

            $this->language->load('support/tickets');
            $this->load->model('support/support');

            $filter = new \Support\TicketFilter();

            $filter->setProjectId($project_id);

            if (!empty($filter_user_created = $this->getRequest()->issetPost('filter_user_created'))) {
                $filter->setCreatedByUid($filter_user_created);
                $data['filter_user_created'] = $filter_user_created;
            } else {
                $data['filter_user_created'] = -1;
            }

            if (!empty($filter_user_delegated = $this->getRequest()->issetPost('filter_user_delegated'))) {
                $filter->setDelegatedToUid($filter_user_delegated);
                $data['filter_user_delegated'] = $filter_user_delegated;
            } else {
                $data['filter_user_delegated'] = -1;
            }

            if (!empty($filter_status = $this->getRequest()->issetPost('filter_status'))) {
                $filter->setStatus($filter_status);
                $data['filter_status'] = $filter_status;
            } else {
                $filter->setStatus(\Support\Status::FILTER_ALL);
                $data['filter_status'] = 0;
            }

            if (!empty($filter_category = $this->getRequest()->issetPost('filter_category'))) {
                $filter->setCategoryId($filter_category);
                $data['filter_category'] = $filter_category;
            } else {
                $data['filter_category'] = -1;
            }

            if (!empty($filter_name = $this->getRequest()->issetPost('filter_name'))) {
                $filter->setTitle($filter_name);
                $data['filter_name'] = $filter_name;
            } else {
                $data['filter_name'] = '';
            }

            if (!empty($page = $this->getRequest()->issetPost('page'))) {
                $filter->setPage($page);
                $data['page'] = $page;
            } else {
                $data['page'] = '';
            }

            if (!empty($filter_limit = $this->getRequest()->issetPost('filter_limit'))) {
                $filter->setLimit($filter_limit);
                $data['filter_limit'] = $filter_limit;
            } else {
                $data['filter_limit'] = '';
            }

            if (!empty($is_one_page = $this->getRequest()->issetPost('filter_as_one_page'))) {
                $filter->setIsOnePage($is_one_page);
                $data['filter_as_one_page'] = $is_one_page;
            } else {
                $data['filter_as_one_page'] = false;
            }

            if (!empty($sort_field = $this->getRequest()->issetPost('sort'))) {
                $filter->setSortField($sort_field);
                $data['sort'] = $sort_field;
            } else {
                $data['sort'] = 'id';
            }

            if (!empty($sort_dir = $this->getRequest()->issetPost('sort_dir'))) {
                $filter->setSortDirection($sort_dir);
                $data['sort_dir'] = $sort_dir;
            } else {
                $data['sort_dir'] = 'ASC';
            }


            $results = $this->model_support_support->getTickets($filter);
            $data['tickets'] = array();

            foreach ($results as $result) {
                $ticket = new \Support\Ticket($this->registry);
                $ticket->mapData($result);
                if (($ticket->getStatus() == \Support\Status::PROGRESS_ID) ||
                    ($ticket->getStatus() == \Support\Status::NEW_ID)
                ) {
                    $ticket->setFinish(nowMySQLTimestamp());
                }

                $ticket->setProgressPercents(100);

                if ($ticket->getStatus() == \Support\Status::NEW_ID) {
                    $ticket->setProgressPercents(0);
                }
                //progress
                $data['tickets'][] = $ticket;
            }

            $json['modal'] = $this->getLoader()->view('support/modal_diagram_gant', $data);
            $json['success'] = true;
        } else {
            $errors[] = $this->getLanguage()->get('error_get_comment_modal');
        }

        if (!empty($errors)) {
            $json['error'] = true;
            $json['errors'] = $errors;
        }

        $app->setOutput($json);
    }

    //user permissions
    public function autocompleteUser()
    {
        $app = new \Tools\Monsters\ApplicationJson($this->registry);
        $json = array();

        if (($app->getRequest()->isRequestMethodPost()) && (!empty($search = $app->getRequest()->issetPost('search')))) {
            $this->getLoader()->model('user/user');
            $filter['filters'] = array(
                'status' => 1,
                'search' => $search
            );

            $results = $this->model_user_user->getUsers($filter);
            if (!empty($results)) {
                $json['success'] = true;
            } else {
                $json['success'] = false;
            }

            foreach ($results as $result) {
                $json['users'][] = array(
                    'uid' => $result['user_id'],
                    'fullname' => $result['fullname']
                );
                $json['info'][] = $result['fullname'];
            }
        } else {
            $json['success'] = false;
        }

        $app->setOutput($json);
    }

    //user permissions
    public function deleteUser()
    {
        $app = new \Tools\Monsters\ApplicationJson($this->registry);
        $json = array();

        if ($app->getRequest()->isRequestMethodPost() &&
            (!empty($project_id = $app->getRequest()->issetPost('project_id'))) &&
            (!empty($uid = $app->getRequest()->issetPost('uid'))) && $this->validateSetting()) {

            $this->getLoader()->model('support/support');
            $json['success'] = $this->model_support_support->deleteUserFromProject($project_id, $uid);
        } else {
            $json['error'] = true;
        }

        $app->setOutput($json);
    }

    public function timelineModal()
    {
        $app = new \Tools\Monsters\ApplicationJson($this->registry);

        $json = array();
        $data = array();
        $errors = array();

        if (($app->getRequest()->isRequestMethodPost()) &&
            ($project_id = $this->getRequest()->issetGet('project_id')) &&
            ($timestamp = $this->getRequest()->issetPost('timestamp'))
        ) {

            $this->getLanguage()->load('support/tickets');
            $this->getLoader()->model('support/support');

            $created = $this->model_support_support->getTimeLineCreatedTicketsByDay($project_id, $timestamp);
            $started = $this->model_support_support->getTimeLineStartedTicketsByDay($project_id, $timestamp);
            $finished = $this->model_support_support->getTimeLineFinishedTicketsByDay($project_id, $timestamp);

            $data['timeline'] = array();

            foreach ($created as $info) {
                $ticket = createTicketFromArray($this->registry, $info);
                $ticket->setIcon('<span><i class="fa fa-plus"></i></span>');
                $data['timeline'][date('H:i', strtotime($info['created']))] = $ticket;
            }

            foreach ($started as $info) {
                $ticket = createTicketFromArray($this->registry, $info);
                $ticket->setIcon('<span><i class="fa fa-play"></i></span>');
                $data['timeline'][date('H:i', strtotime($info['start']))] = $ticket;
            }

            foreach ($finished as $info) {
                $ticket = createTicketFromArray($this->registry, $info);
                $ticket->setIcon('<span><i class="fa fa-flag-checkered"></i></span>');
                $data['timeline'][date('H:i', strtotime($info['finish']))] = $ticket;
            }

            $data['comments'] = array();

            $comments = $this->model_support_support->getTimeLineTicketCommentsByDay($project_id, $timestamp);
            foreach ($comments as $info) {
                $ticket = createTicketFromArray($this->registry, $info);
                $ticket->setIcon('<span><i class="fa fa-pen"></i></span>');
                $ticket->comment = createCommentFromArray($this->registry, $info);
                $data['timeline'][date('H:i', strtotime($ticket->comment->getDatetime()))] = $ticket;
            }

            ksort($data['timeline'], SORT_ASC);

            $obj = new \Support\Comment($this->registry);
            $data['date'] = date('Y-m-d', strtotime($timestamp));
            $data['date_locale'] = $obj->formatDateAsLongString($timestamp);
            $json['modal'] = $this->getLoader()->view('support/modal_day_timeline', $data);
            $json['success'] = true;
        } else {
            $errors[] = $this->getLanguage()->get('error_get_comment_modal');
        }

        if (!empty($errors)) {
            $json['error'] = true;
            $json['errors'] = $errors;
        }

        $app->setOutput($json);
    }

}