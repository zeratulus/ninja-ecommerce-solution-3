<?php

use Ninja\NinjaController;
use Sendpulse\RestApi\Storage\FileStorage;

class ControllerIntegrationSendPulse extends NinjaController
{
    const API_USER_ID = 'config_sendpulse_api_user_id';
    const API_SECRET = 'config_sendpulse_api_secret';
    const BOOK_ID = 'config_sendpulse_address_book_id';

    private $api;

    private function initApi()
    {
        try {
            $this->api = new Sendpulse\RestApi\exApiClient(
                $this->getConfig()->get(self::API_USER_ID),
                $this->getConfig()->get(self::API_SECRET),
                new FileStorage(DIR_STORAGE)
            );
        } catch (Exception $e) {
            $this->getLog()->write($e->getMessage());
        }
    }

    public function index()
    {
        $this->getResponse()->redirect($this->getUrl()->link('error/not_found'));
    }

    public function addToAddressBookEazyway($args)
    {
        if (!isset($args['user']))
            throw new Exception('ControllerIntegrationSendpulse -> No user data in args');

        $this->initApi();
        return $this->api->addEmailFromOpenCartUserWithVariables($args['user'], $this->getConfig()->get(self::BOOK_ID));
    }

    public function sendAutomation360Event(string $eventName, array $data)
    {
        if (!empty($eventName) && !empty($data)) {
            $this->initApi();
            $this->api->startEventAutomation360($eventName, $data);
        }
    }

}