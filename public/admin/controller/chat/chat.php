<?php

class ControllerChatChat extends \Ninja\AdminController
{
    public string $route = 'chat/chat';
    public string $model = 'model_chat_chat';

    const TYPE_CUSTOMER = 'Customer';
    const TYPE_USER = 'User';

    public function index()
    {
        $this->getLoader()->language($this->route);
        $this->getLoader()->model($this->route);

        $data['breadcrumbs'] = $this->makeDefaultBreadcrumbs($this->route);

        $chats = $this->{$this->model}->getChatByMember($this->getUser()->getId(), self::TYPE_USER);

        $ids = [];
        foreach ($chats as $chat) {
            $ids[] = $chat['chat_id'];
        }
        $data['chat_members'] = [];
        $chat_members = [];
        $data['last_chat_messages'] = [];
        if (!empty($ids)) {
            $messages = $this->{$this->model}->getChatLastMessagesList($ids);
            foreach ($messages as $message) {
                $data['last_chat_messages'][$message['chat_id']] = $message;
            }

            $chat_members = $this->{$this->model}->getChatMembersByChatIds($ids);
        }
        foreach ($chat_members as $chat_member) {
            $data['chat_members'][$chat_member['id']] = $chat_member;
        }

        $this->getLoader()->model('customer/customer');
        $data['customers'] = [];
        $customers = $this->model_customer_customer->getCustomers(['limit' => 100000]);
        foreach ($customers as $customer) {
            $data['customers'][$customer['customer_id']] = $customer;
        }

        $this->getLoader()->model('user/user');
        $data['users'] = [];
        $users = $this->model_user_user->getUsers(['limit' => 100000]);
        foreach ($users as $user) {
            $data['users'][$user['user_id']] = $user;
        }

        $data['chats'] = [];
        foreach ($chats as $chat) {
            $members = [];
            foreach ($data['chat_members'] as $chat_member) {
                if ($chat_member['chat_id'] == $chat['chat_id'])
                    $members[$chat_member['id']] = $chat_member;
            }
            $data['chats'][$chat['chat_id']] = [
                'chat' => $chat,
                'members' => $members,
                'last_message' => $data['last_chat_messages'][$chat['chat_id']]
            ];
        }

        $data['user_id'] = $this->getUser()->getId();
        $data['user_group_id'] = $this->getUser()->getGroupId();
        $data['user_type'] = self::TYPE_USER;

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view($this->route, $data));
    }

}