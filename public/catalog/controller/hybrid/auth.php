<?php

class ControllerHybridAuth extends Controller {

	private $_route = 'hybrid/auth';
    private $_config = array();
    private $_redirect;

    public function index() {

        $this->_prepare();

        // Check if Logged
        if ($this->customer->isLogged()) {
            $this->response->redirect($this->_redirect);
        }

        // Check if module is Enabled
        if (!$this->config->get('hybrid_auth_status')) {
            $this->response->redirect($this->_redirect);
        }

	    // Receive request
	    if (isset($this->request->get['provider'])) {
		    $provider = $this->request->get['provider'];
	    } else {
		    // Save error to the System Log
		    $this->log->write('Missing application provider.');

		    // Set Message
		    $this->session->data['error'] = sprintf("An error occurred, please <a href=\"%s\">notify</a> the administrator.",
			    $this->url->link('information/contact', '', true));

		    // Redirect to the Login Page
		    $this->response->redirect($this->_redirect);
	    }

        // Dependencies
        $this->language->load('hybrid/auth');

        $this->load->model('hybrid/auth');

        // Load Config
        $this->_config['callback'] = $this->url->link($this->_route, "&provider={$provider}", true);
        $this->_config['debug_file'] = DIR_LOGS . 'hybridauth.log';
        $this->_config['debug_mode'] = (bool) $this->config->get('hybrid_auth_debug');
        $this->_config['providers'] = $this->providers();

        try {
            // Authentication Begin
            $auth = new Hybridauth\Hybridauth($this->_config);
            $adapter = $auth->authenticate($provider);
            $user_profile = $adapter->getUserProfile();

            // 1 - check if user already have authenticated using this provider before
            $customer_id = $this->model_hybrid_auth->findCustomerByIdentifier($provider, $user_profile->identifier);

            if ($customer_id) {
                // 1.1 Login
                $this->model_hybrid_auth->login($customer_id);

                // 1.2 Redirect to Refer Page
                $this->response->redirect($this->_redirect);
            }


            // 2 - else, here lets check if the user email we got from the provider already exists in our database ( for this example the email is UNIQUE for each user )
            // if authentication does not exist, but the email address returned  by the provider does exist in database,
            // then we tell the user that the email  is already in use
            // but, its up to you if you want to associate the authentication with the user having the address email in the database
            if ($user_profile->email){
                $customer_id = $this->model_hybrid_auth->findCustomerByEmail($user_profile->email);

                if ($customer_id) {
                    $this->session->data['success'] = sprintf($this->language->get('text_provider_email_already_exists'), $provider, $user_profile->email);
                    $this->response->redirect($this->url->link('account/login', '', true));
                }
            }

            // 3 - if authentication does not exist and email is not in use, then we create a new user
            $user_address = array();

            if (!empty($user_profile->address)) {
                $user_address[] = $user_profile->address;
            }

            if (!empty($user_profile->region)) {
                $user_address[] = $user_profile->region;
            }

            if (!empty($user_profile->country)) {
                $user_address[] = $user_profile->country;
            }

            // 3.1 - create new customer
            $customer_id = $this->model_hybrid_auth->addCustomer(
                array('email'      => $user_profile->email,
                      'firstname'  => $user_profile->firstName,
                      'lastname'   => $user_profile->lastName,
                      'telephone'  => $user_profile->phone,
                      'fax'        => false,
                      'newsletter' => true,
                      'company'    => false,
                      'address_1'  => ($user_address ? implode(', ', $user_address) : false),
                      'address_2'  => false,
                      'city'       => $user_profile->city,
                      'postcode'   => $user_profile->zip,
                      'country_id' => $this->model_hybrid_auth->findCountry($user_profile->country),
                      'zone_id'    => $this->model_hybrid_auth->findZone($user_profile->region),
                      'password'   => substr(rand().microtime(), 0, 6)
                )
            );

            // 3.2 - create a new authentication for him
            $this->model_hybrid_auth->addAuthentication(
                array('customer_id' => (int) $customer_id,
                    'provider' => $provider,
                    'identifier' => $user_profile->identifier,
                    'web_site_url' => $user_profile->webSiteURL,
                    'profile_url' => $user_profile->profileURL,
                    'photo_url' => $user_profile->photoURL,
                    'display_name' => $user_profile->displayName,
                    'description' => $user_profile->description,
                    'first_name' => $user_profile->firstName,
                    'last_name' => $user_profile->lastName,
                    'gender' => $user_profile->gender,
                    'language' => $user_profile->language,
                    'age' => $user_profile->age,
                    'birth_day' => $user_profile->birthDay,
                    'birth_month' => $user_profile->birthMonth,
                    'birth_year' => $user_profile->birthYear,
                    'email' => $user_profile->email,
                    'email_verified' => $user_profile->emailVerified,
                    'phone' => $user_profile->phone,
                    'address' => $user_profile->address,
                    'country' => $user_profile->country,
                    'region' => $user_profile->region,
                    'city' => $user_profile->city,
                    'zip' => $user_profile->zip
                )
            );

            // 3.3 - login
            $this->model_hybrid_auth->login($customer_id);

            // 3.4 - redirect to Refer Page
            $this->response->redirect($this->_redirect);

       }
	        /**
	         * Catch Curl Errors
	         *
	         * This kind of error may happen when:
	         *     - Internet or Networks issues.
	         *     - Your server configuration is not setup correctly.
	         * The full list of curl errors that may happen can be found at http://curl.haxx.se/libcurl/c/libcurl-errors.html
	         */
        catch (Hybridauth\Exception\HttpClientFailureException $e) {
	        echo 'Curl text error message : ' . $adapter->getHttpClient()->getResponseClientError();
        }

	        /**
	         * Catch API Requests Errors
	         *
	         * This usually happen when requesting a:
	         *     - Wrong URI or a mal-formatted http request.
	         *     - Protected resource without providing a valid access token.
	         */
        catch (Hybridauth\Exception\HttpRequestFailedException $e) {
	        echo 'Raw API Response: ' . $adapter->getHttpClient()->getResponseBody();
        }

	        /**
	         * This fellow will catch everything else
	         */
        catch (\Exception $e) {
	        echo 'Oops! We ran into an unknown issue: ' . $e->getMessage();
        }

    }
    
    
    private function _prepare() {

        // Some API returns encoded URL
        if (isset($this->request->get) && isset($_GET)) {

            // Prepare for OpenCart
            foreach ($this->request->get as $key => $value) {
                $this->request->get[str_replace('&amp;', '', $key)] = $value;
            }

            // Prepare for Library
            foreach ($_GET as $key => $value) {
                $_GET[str_replace('&amp;', '&', $key)] = $value;
            }
        }

        // Base64 URL Decode
        if (isset($this->request->get['redirect'])) {
            $this->_redirect = base64_decode($this->request->get['redirect']);
        } else {
            $this->_redirect = $this->url->link('account/edit', '', true);
        }
    }

	public function providers()
	{
		$results = array();

		$settings = $this->config->get('hybrid_auth');
		foreach ($settings as $config) {
			$results[$config['provider']] = array(
				'enabled' => (bool)$config['enabled'],
				'keys' => array(
					'id'     => $config['key'],
					'key'    => $config['key'],
					'secret' => $config['secret'],
					'scope'  => isset($config['scope']) ? $config['scope'] : ''
				)
			);
		}

		return $results;
    }

    //Example of render call in your place...
    //$data['hybridauth'] = $this->load->controller('hybrid/auth/render');
	public function render()
	{
		$this->language->load('hybrid/auth');
		$data = array();
		$data['status'] = $this->config->get('hybrid_auth_status');
		$data['providers'] = array();
		$providers = $this->providers();
		foreach ($providers as $provider => $config) {
			if ($config['enabled']) {
				$image = strtolower($provider);

				$data['providers'][] = array(
					'href' => $this->url->link($this->_route, "&provider={$provider}", true),
					'image' => "image/hybridauth/{$image}.png",
					'title' => $provider
				);
			}
		}

		return $this->load->view($this->_route, $data);
    }

}