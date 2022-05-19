<?php

namespace Sendpulse\RestApi;

class exApiClient extends ApiClient
{

    /**
     * Adds single email to address book with variables
     * @param array $user
     * @param int $bookID AddressBook Id From Sendpulse
     * @return \stdClass
     */
    public function addEmailFromOpenCartUserWithVariables($user, $bookID)
    {
        //https://sendpulse.com/integrations/api/bulk-email#add-email
        $variables = new \stdClass();
        $variables->Phone = $user['telephone'];
        $variables->имя = $user['firstname'];
        $variables->фамилия = $user['lastname'];

        $data = new AddressBookSingleUserData();
        $data->emails = [
            0 => [ //this must be array in json for processing by endpoint
                "email" => $user["email"],
                "variables" => $variables
            ]
        ];

        $requestResult = $this->sendRequest('addressbooks/' . $bookID . '/emails', 'POST', $data);

        return $this->handleResult($requestResult);
    }

}

//this class fix error when $api->sendRequest
class AddressBookSingleUserData implements \Countable
{

    public function count()
    {
        return 1;
    }

}
