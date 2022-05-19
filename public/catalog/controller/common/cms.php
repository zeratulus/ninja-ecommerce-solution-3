<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 19.02.20
 * Time: 13:48
 */

class ControllerCommonCms extends \Ninja\Controller {

	private function getFakeAddress() {
		return array(
			'address_id'     => null,
			'firstname'      => '',
			'lastname'       => '',
			'company'        => '',
			'address_1'      => '',
			'address_2'      => '',
			'postcode'       => '',
			'city'           => '',
			'zone_id'        => null,//$this->getConfig()->get('config_zone_id'),
			'zone'           => '',
			'zone_code'      => '',
			'country_id'     => null,//$this->getConfig()->get('config_country_id'),
			'country'        => '',
			'iso_code_2'     => '',
			'iso_code_3'     => '',
			'address_format' => '',
			'custom_field'   => ''
		);
	}

    public function index()
    {
        //This is Controller for CMS Utils
    }


	/**
	 * Function for getTelephonesLinks
	 * @param $value
	 * @return array
	 */
	private function createPhoneItem($value) {
	    $phone_data = array();
	    $phone_data['telephone'] = $value;
	    $phone_data['href'] = clearPhoneNumber($value);
	    return $phone_data;
    }

    public function getTelephonesLinks()
    {
        //Telephones
        $data = array();

        $telephones = $this->getConfig()->get('config_telephone');
        //more than one phone number
        if (strpos($telephones, ',') !== false) {
            $phones = explode(',', $telephones);
            foreach ($phones as $key => $value) {
                $data[] = $this->createPhoneItem($value);
            }
        } else { //one phone number
            $data[] = $this->createPhoneItem($telephones);
        }

        return $data;
    }

    public function getAnalyticsScripts()
    {
        // Analytics
        $this->load->model('setting/extension');

        $data = array();

        $analytics = $this->model_setting_extension->getExtensions('analytics');

        foreach ($analytics as $analytic) {
            if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
                $data[] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
            }
        }

        return $data;
    }

	public function getPaymentMethods()
	{
		$total = 1;
		// Payment Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('payment');
//        $this->getDebugBar()->getMessages()->addMessage($results);
//		$recurring = $this->cart->hasRecurringProducts();

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);

				if (!empty($this->session->data['payment_address'])) {
					$address_data = $this->session->data['payment_address'];
				} else {
					//Fake address for get enabled payment methods
					$address_data = $this->getFakeAddress();
				}

				$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($address_data, $total);
//                $this->getDebugBar()->getMessages()->addMessage($method);
//				if ($method) {
//					if ($recurring) {
//						if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
//							$method_data[$result['code']] = $method;
//						}
//					} else {
//						$method_data[$result['code']] = $method;
//					}
//				}
				if ($method)
					$method_data[$result['code']] = $method;
			}
		}

		return $method_data;
    }

	public function getShippingMethods()
	{
		// Shipping Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = $this->model_setting_extension->getExtensions('shipping');

		if (!empty($this->session->data['shipping_address'])) {
			$address_data = $this->session->data['shipping_address'];
		} else {
			//Fake address for get enabled payment methods
			$address_data = $this->getFakeAddress();
		}

		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);

				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($address_data);

                $method_data[$result['code']] = $quote;
			}
		}

		return $method_data;
    }

	public function getLanguageAlternateLinks()
	{
		$uri = $this->getRequest()->server['REQUEST_URI'];

		$this->getLoader()->model('design/seo_url');

		$isQuery = false;
		//check link on rewrite
		if (strpos($uri, '?route=') !== false) {
			//get id and find alternate link for each language by query
			$route = extractRoute($this->getRequest()->server['REQUEST_URI']);

			$queries = getSeoUrlQueriesKeys();
			foreach ($queries as $query) {
				if (($pos = utf8_strpos($uri, $query)) !== false) {
					$isQuery = true;
					$currentQuery = $query;
					break;
				};
			}

			if ($isQuery) {
				$value = getUrlParamValue($uri, $currentQuery);
				$query = "{$currentQuery}={$value}";
			} else {
				$query = $route;
			}
			$urls = $this->model_design_seo_url->getSeoUrlsByQuery($query);
		} else {
			//get alternate link for each language by keyword
			$keyword = getUrlPathLast($uri);
			$urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
		}

		$languages = $this->getLanguagesList();

//		$results = array();
		$results = '';
		if (!empty($urls)) {
			foreach ($urls as $seo_url) {
				if ($this->language_id != $seo_url['language_id']) {

					foreach ($languages as $language) {
						if ($language['language_id'] == $seo_url['language_id']) {
							$code = $language['code'];
							break;
						}
					}

					if ($isQuery) {
						$href = $this->getUrl()->link($route, "{$seo_url['query']}&lang={$code}");
					} else {
						if ($this->getConfig()->get('config_secure')) {
							$href = $this->getUrl()->getSsl() . $seo_url['keyword'] . "&lang={$code}";
						} else {
							$href = $this->getUrl()->getUrl() . $seo_url['keyword'] . "&lang={$code}";
						}
					}

//				$results[] = array(
//					'href' => $href,
//					'code' => $code
//				);

					$results .= "<link rel=\"alternate\" hreflang=\"{$code}\" href=\"{$href}\" />";
				}
			}
		} else {
			//common/home
		}

		return $results;
    }
    
}