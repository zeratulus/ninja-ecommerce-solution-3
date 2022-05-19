<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 16.09.19
 * Time: 22:44
 */

class ControllerSupportProjects extends \Ninja\AdminController {

    private $error = array();

    public function index()
    {
        $this->getLoader()->language('support/projects');
        $this->getDocument()->setTitle($this->language->get('heading_title'));
        $this->getLoader()->model('support/support');

        $data['user_token'] = $this->getSession()->data['user_token'];

        $url = '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getUserToken())
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link('support/projects', 'user_token=' . $this->getUserToken() . $url)
        );

        $this->processWarningMessage($data);
        $this->processSuccessMessage($data);
        $this->processSelected($data);

        $data['add'] = $this->getUrl()->link('support/projects/add', "user_token={$data['user_token']}{$url}");
        $data['delete'] = $this->getUrl()->link('support/projects/delete', "user_token={$data['user_token']}{$url}");

        $data['projects'] = array();

        //if admin get all projects else get by user permissions - task #23
        if ($this->getUser()->getGroupId() == \Support\User::ADMIN_GROUP_ID) {
            $results = $this->model_support_support->getProjects();
        } else {
            $results = $this->model_support_support->getProjectsByPermissions($this->getUser()->getId());
        }
        foreach ($results as $row) {
            $project = new \Support\Project($this->registry);
            $project->mapData($row);
            $data['projects'][] = $project;
            if (end($results) === $row) {
                $data['last_project_id'] = $project->getId();
            }
        }

        $data['counter'] = 0;

        //Get Current User Info
        $this->getLoader()->model('user/user');
        $user_info = $this->model_user_user->getUserById($this->user->getId());
        $user = new \Support\User($this->registry);
        $user->mapData($user_info);
        $data['user'] = $user;

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view('support/projects', $data));
    }

    public function add()
    {
        $this->getLoader()->language('support/projects');
        $this->getDocument()->setTitle($this->language->get('heading_title'));
        $this->getLoader()->model('support/support');

        if ( ($this->getRequest()->server['REQUEST_METHOD'] == 'POST') && ($this->validateForm()) ) {
            $project = new \Support\Project($this->registry);
            $project->mapData($this->getRequest()->post);
            $result = $this->model_support_support->addProject($project);
            if ($result > 0) {
                $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');
                $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$this->getUserToken()}"));
            }
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->getLoader()->language('support/projects');
        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getLoader()->model('support/support');

        if (($this->getRequest()->isRequestMethodPost()) && ($this->validateForm())) {
            if (!empty($project_id = $this->getRequest()->issetGet('project_id'))) {
                $project = new \Support\Project($this->registry);
                $project->mapData($this->getRequest()->post);
                $result = $this->model_support_support->editProject($project);
                if ($result > 0) {
                    $this->getSession()->data['success'] = $this->getLanguage()->get('text_success_edit');
                    $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$this->getUserToken()}"));
                }
            }
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->getLoader()->language('support/projects');

        if ( ($this->getRequest()->isRequestMethodPost()) && ($selected = $this->getRequest()->issetPost('selected')) ) {
            $this->getLoader()->model('support/support');

            foreach ($selected as $id) {
                $result = $this->model_support_support->removeProject($id);
                if ($result > 0) {
                    $this->getSession()->data['success'] = $this->getLanguage()->get('text_success_delete');
                }
            }
        }

        $this->getResponse()->redirect($this->getUrl()->link('support/projects', "user_token={$this->getUserToken()}"));
    }

    protected function getForm() {
        $data['user_token'] = $this->getUserToken();
        $isAdd = !isset($this->getRequest()->get['project_id']);
        $data['text_form'] = $isAdd ? $this->getLanguage()->get('text_add') : $this->getLanguage()->get('text_edit');

        $this->getDocument()->setTitle($data['text_form']);
        $this->getLoader()->model('support/support');

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

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', "user_token={$data['user_token']}")
        );

        $route_args = "user_token={$data['user_token']}";
        if (!empty($this->getRequest()->get['project_id'])) {
            $route_args .= "&project_id={$this->getRequest()->get['project_id']}";
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link('support/projects', $route_args)
        );

        if ($isAdd) {
            $data['action'] = $this->getUrl()->link('support/projects/add', "user_token={$data['user_token']}{$url}", true);
        } else {
            $data['action'] = $this->getUrl()->link('support/projects/edit', "user_token={$data['user_token']}{$url}", true);
        }

        $data['breadcrumbs'][] = array(
            'text' => $data['text_form'],
            'href' => $data['action']
        );

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['cancel'] = $this->getUrl()->link('support/projects', "user_token={$data['user_token']}{$url}", true);

        //get info if edit
        if ((!$isAdd) && (!$this->getRequest()->isRequestMethodPost())) {
            $project_info = $this->model_support_support->getProjectById($this->getRequest()->get['project_id']);
        }

        //Prepare Form Data
        if (isset($this->getRequest()->post['title'])) {
            $data['title'] = $this->getRequest()->post['title'];
        } elseif (!$isAdd) {
            $data['title'] = $project_info['title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->getRequest()->post['description'])) {
            $data['description'] = $this->getRequest()->post['description'];
        } elseif (!$isAdd) {
            $data['description'] = $project_info['description'];
        } else {
            $data['description'] = '';
        }

        if (isset($this->getRequest()->post['created'])) {
            $data['created'] = $this->getRequest()->post['created'];
        } elseif (!$isAdd) {
            $data['created'] = $project_info['created'];
        } else {
            $data['created'] = nowMySQLTimestamp();
        }

        if (isset($this->getRequest()->post['status'])) {
            $data['status'] = $this->getRequest()->post['status'];
        } elseif (!$isAdd) {
            $data['status'] = $project_info['status'];
        } else {
            $data['status'] = true;
        }

        if (isset($this->getRequest()->post['public'])) {
            $data['public'] = $this->getRequest()->post['public'];
        } elseif (!$isAdd) {
            $data['public'] = $project_info['public'];
        } else {
            $data['public'] = true;
        }

        if (isset($this->getRequest()->post['color'])) {
            $data['color'] = $this->getRequest()->post['color'];
        } elseif (!$isAdd) {
            $data['color'] = $project_info['color'];
        } else {
            $data['color'] = 0;
        }

        if (isset($this->getRequest()->post['icon'])) {
            $data['icon'] = $this->getRequest()->post['icon'];
        } elseif (!$isAdd) {
            $data['icon'] = $project_info['icon'];
        } else {
            $data['icon'] = 0;
        }

        if (isset($this->getRequest()->post['timezone'])) {
            $data['timezone'] = $this->getRequest()->post['timezone'];
        } elseif (!$isAdd) {
            $data['timezone'] = $project_info['timezone'];
        } else {
            $data['timezone'] = -1;
        }

        $data['errors'] = print_r($this->error, true);

        $data['colors'] = \Support\Project::$colorsList;
        $data['icons'] = \Support\Project::$iconsConfigList;

        $data['timezones'] = timezone_identifiers_list();

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view('support/project_form', $data));
    }

    protected function validateForm()
    {
        if (!$this->getUser()->hasPermission('modify', 'support/projects')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        //TODO: Server-side Validation
        if (empty($this->getRequest()->post['title'])) {

        }

        if (empty($this->getRequest()->post['description'])) {

        }

        if (($project_id = $this->getRequest()->issetGet('project_id')) >= 0) {
            $this->getRequest()->post['id'] = $project_id;
        }

        return empty($this->error);
    }

}