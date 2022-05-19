<?php

class ModelExtensionShippingNovaposhta extends Model {

    function getQuote($address) {
        $this->load->language('extension/shipping/novaposhta');

        if ($this->config->get('shipping_novaposhta_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('shipping_novaposhta_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('shipping_novaposhta_geo_zone_id')) {
                $status = true;
            } elseif ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $quote_data = array();

            $cost = 0.00;			
			
            if ($this->config->get('shipping_novaposhta_min_total_for_free_delivery') > $this->cart->getSubTotal()) {
                $cost = ( ($this->cart->getWeight() * $this->config->get('shipping_novaposhta_delivery_price')) +
                    $this->config->get('shipping_novaposhta_delivery_order') +
                    ( $this->cart->getSubTotal() * $this->config->get('shipping_novaposhta_delivery_insurance') / 100) +
                    ($this->cart->getSubTotal() * $this->config->get('shipping_novaposhta_delivery_nal') / 100)
                );
            }

            $quote_data['novaposhta'] = array(
                'code' => 'novaposhta.novaposhta',
                'title' => $this->language->get('text_description'),
                'cost' => $cost,
                'tax_class_id' => 0,
                'text' => $this->currency->format($cost, $this->session->data['currency'])
            );

            $method_data = array(
                'code' => 'novaposhta',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('shipping_novaposhta_sort_order'),
                'error' => false
            );
        }

        return $method_data;
    }

    public function getWarehouses($data = array()) {
        $sql = 'SELECT * FROM np_warehouses npw';

        if (isset($data['filter_name']) || isset($data['filter_address']) || isset($data['filter_city'])) {
            $sql .= ' WHERE ';
        }

        $implode = array();

        if (isset($data['filter_name'])) {
            $filter = ' (npw.description LIKE ("%' . $data['filter_name'] . '%")';
            $filter .= ' OR npw.descriptionRu LIKE ("%' . $data['filter_name'] . '%"))';
            $implode[] = $filter;
        }

        if (isset($data['filter_address'])) {
            $filter = ' (npw.shortAddress LIKE ("%' . $data['filter_address'] . '%")';
            $filter .= ' OR npw.shortAddressRu LIKE ("%' . $data['filter_address'] . '%"))';
            $implode[] = $filter;
        }

        if (isset($data['filter_city'])) {
            $filter = ' (npw.CityDescription LIKE ("%' . $data['filter_city'] . '%")';
            $filter .= ' OR npw.CityDescriptionRu LIKE ("%' . $data['filter_city'] . '%"))';
            $implode[] = $filter;
        }

        $sql .= implode(' AND ', $implode);

        $sort_data = array(
            'Description',
            'DescriptionRu',
            'ShortAddress',
            'ShortAddressRu',
            'CityDescription',
            'CityDescriptionRu'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY npw." . $data['sort'];
        } else {
            $sql .= " ORDER BY npw.DescriptionRu";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        return $this->db->query($sql)->rows;
    }

    public function getCities() {
        $sql = 'SELECT DISTINCT npw.CityDescription as city_ua, npw.CityDescriptionRu as city_ru FROM np_warehouses npw';

        $implode = [];
        if (isset($data['filter_city'])) {
            $filter = ' (npw.CityDescription LIKE ("%' . $data['filter_city'] . '%")';
            $filter .= ' OR npw.CityDescriptionRu LIKE ("%' . $data['filter_city'] . '%"))';
            $implode[] = $filter;
        }

        if (!empty($implode)) {
            $sql .= ' WHERE ';
            $sql .= implode(' AND ', $implode);
        }

        $sort_data = array(
            'Description',
            'DescriptionRu',
            'ShortAddress',
            'ShortAddressRu',
            'CityDescription',
            'CityDescriptionRu'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY npw." . $data['sort'];
        } else {
            $sql .= " ORDER BY npw.CityDescription";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        return $this->getDb()->query($sql)->rows;
    }


}
