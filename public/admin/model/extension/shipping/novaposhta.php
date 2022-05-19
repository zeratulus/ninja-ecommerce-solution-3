<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 11.12.17
 * Time: 6:58
 */

class ModelExtensionShippingNovaposhta extends Model {

    public function isWarehouseExists($siteKey) {
        $sql = 'SELECT SiteKey FROM np_warehouses npw WHERE npw.SiteKey=' . (int)$siteKey . ' LIMIT 1';
        return $this->db->query($sql)->num_rows;
    }

    public function getWarehouse($siteKey) {
        $sql = 'SELECT * FROM np_warehouses npw WHERE npw.SiteKey=' . (int)$siteKey . ' LIMIT 1';
        return $this->db->query($sql)->row;
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

    public function getWarehousesTotal($data = array()) {
        $sql = 'SELECT COUNT(*) AS total FROM np_warehouses npw';

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

        return $this->db->query($sql)->row['total'];
    }

    public function addWarehouse($data) {
        $this->db->query('INSERT INTO np_warehouses (SiteKey, Description, DescriptionRu, ShortAddress, ShortAddressRu, Phone, TypeOfWarehouse, Ref, O_Number, CityRef, CityDescription, CityDescriptionRu, Longitude, Latitude, PostFinance, BicycleParking, PaymentAccess, POSTerminal, InternationalShipping, TotalMaxWeightAllowed, PlaceMaxWeightAllowed, Reception) VALUES ('
            .$data['SiteKey'] .', "'
            .$this->db->escape($data['Description']) .'", "'
            .$this->db->escape($data['DescriptionRu']) . '", "'
            .$this->db->escape($data['ShortAddress']).'", "'
            .$this->db->escape($data['ShortAddressRu']).'", "'
            .$this->db->escape($data['Phone']).'", "'
            .$this->db->escape($data['TypeOfWarehouse']).'", "'
            .$this->db->escape($data['Ref']).'", '
            .$data['Number'].', "'
            .$this->db->escape($data['CityRef']).'", "'
            .$this->db->escape($data['CityDescription']) .'", "'
            .$this->db->escape($data['CityDescriptionRu']) .'", "'
            .$this->db->escape($data['Longitude']).'", "'
            .$this->db->escape($data['Latitude']).'", '
            .$data['PostFinance'].', '
            .$data['BicycleParking'].', '
            .$data['PaymentAccess'].', '
            .$data['POSTerminal'].', '
            .$data['InternationalShipping'].', '
            .$data['TotalMaxWeightAllowed'].', '
            .$data['PlaceMaxWeightAllowed'].', "Reception")');
    }

    public function clearWarehouses() {
        $this->db->query('DELETE FROM np_warehouses');
        return $this->getDb()->countAffected();
    }

    public function isInstalled() {
        return $this->db->query('SELECT * FROM information_schema.tables WHERE table_schema = "'.DB_DATABASE.'" AND table_name = "np_warehouses" LIMIT 1;')->num_rows;
    }

    public function install() {
        $this->db->query('CREATE TABLE np_warehouses ( SiteKey INT NOT NULL , Description TEXT NOT NULL , DescriptionRu TEXT NOT NULL , ShortAddress TEXT NOT NULL , ShortAddressRu TEXT NOT NULL , Phone TEXT NOT NULL , TypeOfWarehouse TEXT NOT NULL , Ref TEXT NOT NULL , O_Number INT NOT NULL , CityRef TEXT NOT NULL , CityDescription TEXT NOT NULL , CityDescriptionRu TEXT NOT NULL , Longitude TEXT NOT NULL , Latitude TEXT NOT NULL , PostFinance INT NOT NULL , BicycleParking INT NOT NULL , PaymentAccess INT NOT NULL , POSTerminal INT NOT NULL , InternationalShipping INT NOT NULL , TotalMaxWeightAllowed INT NOT NULL , PlaceMaxWeightAllowed INT NOT NULL , Reception TEXT NOT NULL ) ENGINE = InnoDB;');
    }

    public function uninstall() {
        $this->db->query('DROP TABLE IF EXISTS ' . DB_PREFIX . 'np_warehouses');
    }

}