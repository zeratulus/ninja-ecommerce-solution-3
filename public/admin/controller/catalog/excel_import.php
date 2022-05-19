<?php

//PHP Excel Reader For admin import
require_once(DIR_SYSTEM . 'library/excel/php-excel-reader/excel_reader2.php');
require_once(DIR_SYSTEM . 'library/excel/SpreadsheetReader.php');
require_once(DIR_SYSTEM . 'library/excel/SpreadsheetReader_XLSX.php');
require_once(DIR_SYSTEM . 'library/excel/SpreadsheetReader_ODS.php');

class ControllerCatalogExcelImport extends \Ninja\NinjaController {

    public function index()
    {
        $this->getLoader()->language('catalog/excel_import');

        $this->getForm();
    }

    public function getForm()
    {
        $data = array();

        $data['heading_title'] = 'XLSX Products & Categories Import';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['importRoyalPrice'] = $this->getUrl()->link('catalog/excel_import/importRoyalPrice', 'user_token=' . $this->getSession()->data['user_token'], true);
        $data['action'] = $data['importRoyalPrice'];

        $data['categories'] = $this->getLoader()->controller('catalog/category/categories_list');
        $data['manufacturers'] = $this->getLoader()->controller('catalog/manufacturer/manufacturers_list');

        $this->response->setOutput($this->load->view('catalog/excel_import_form', $data));
    }

    //Price Royal import
    public function importRoyalPrice()
    {
        //Log to file
        $log = new Log('excel_import_' . replaceSpaces(nowMySQLTimestamp()) . '.log');

        if (($this->getUser()->isLogged()) && ($this->getUser()->getGroupId() == \Support\User::ADMIN_GROUP_ID)) {

            $manufacturer_id = $this->getRequest()->isset_post('manufacturer_id');
            if (empty($manufacturer_id)) {
                $manufacturer_id = 0;
                $brand_name = '';
            } else {
                $this->getLoader()->model('catalog/manufacturer');
                $manufacturer = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);
                $brand_name = $manufacturer['name'] . ' ';
            }

            $is_add_brand_name = (bool)$this->getRequest()->isset_post('is_add_brand_name');

            //parent_category_id for categories in excel file - categories will subcategories of parent
            $parent_category_id = $this->getRequest()->isset_post('parent_category_id');
            if (empty($parent_category_id)) $parent_category_id = 0;

            ini_set('display_errors', 1);

            $start = time();

            if ( (!empty($this->request->files['file']['name'])) && (is_file($this->request->files['file']['tmp_name'])) ) {

                // Check to see if any PHP files are trying to be uploaded
                $content = file_get_contents($this->request->files['file']['tmp_name']);

                if (preg_match('/\<\?php/i', $content)) {
                    die('Error: PHP verification failed!');
                }

                $new_filename = str_replace(' ','-', nowMySQLTimestamp()) . '_excel_import_' . token(5) . '.' . getFileExt($this->request->files['file']['name']);
                move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $new_filename);
            } else {
                die('No file.');
            }

            $log->write('File import started: ' . DIR_UPLOAD . $new_filename);

            $this->getLoader()->model('localisation/language');
            $this->getLoader()->model('catalog/product');
            $this->getLoader()->model('catalog/category');

            $languages = $this->model_localisation_language->getLanguages();

            //prepare SEO URL data
            $seo = array();
            foreach ($languages as $language) {
                //ru-ru to ru
                $code = explode('-', $language['code']);

                $seo[] = array(
                    'language_id' => $language['language_id'],
                    'prefix' => $code[0]
                );
            }

            $reader = new SpreadsheetReader(DIR_UPLOAD . $new_filename);
//            $isSkip = false;
            $category_id = 0;
            $store_id = 0;

            $sheets = $reader->Sheets();

//            var_dump($sheets);

//            foreach ($sheets as $index => $name) {
            //index of needed sheet
                $index = 1; //get Sheet Лист 2 (by format)

                $sheet_name = $sheets[$index];

                $reader->ChangeSheet($index);

                //excel table header (by format)
                $reader->next();
                $row = $reader->next();
//                var_dump($row);

                //validate document by header
                $model = isset($row[0]) ? $row[0] : '';   //Код
                $name = isset($row[1]) ? $row[1] : '';    //Найменування
                $quantity = isset($row[2]) ? $row[2] : 0; //К-ть (шт)
                $weight = isset($row[3]) ? $row[3] : 0;   //Фасування (кг)
                $price = isset($row[5]) ? $row[5] : '';   //Рекомендована ціна з ПДВ*

                if ( $model === 'Код' &&
                    $name === 'Найменування' &&
                    $quantity === 'К-ть (шт)' &&
                    $weight === 'Фасування (кг)' &&
                    $price === 'Рекомендована ціна з ПДВ*'
                ) {
                    $isValid = true;
                } else {
                    $isValid = false;
                }

                //parse data, each row is product or category
                if ($isValid) {

                    $reader->rewind();

                    $line = 1;
                    foreach ($reader as $row) {
                        if ($line > 3) { //skip headings - feature of excel file

//                            echo $line;
//                            var_dump($row);
//                            echo '<br><br>';

                            //product settings from excel
                            $model = isset($row[0]) ? $row[0] : '';   //Код
                            $name = isset($row[1]) ? $row[1] : '';    //Найменування
                            $quantity = isset($row[2]) ? $row[2] : 0; //К-ть (шт)
                            $weight = isset($row[3]) ? $row[3] : 0;   //Фасування (кг)
                            $price = isset($row[5]) ? $row[5] : '';   //Рекомендована ціна з ПДВ*


                            //product
                            if (!empty($model) && !empty($name) && !empty($weight) && !empty($price)) {

                                if ($is_add_brand_name) {
                                    if (!empty($brand_name) && (strpos($name, trim($brand_name)) === false)) {
                                        $name = $brand_name . $name;
                                    }
                                }

                                //fill data and defaults
                                $product = array(
                                    'sku' => '',
                                    'upc' => '',
                                    'ean' => '',
                                    'jan' => '',
                                    'isbn' => '',
                                    'mpn' => '',
                                    'location' => '',
                                    'main_category_id' => $category_id,
                                    'model' => $model,
                                    'weight' => $weight,
                                    'quantity' => $quantity,
                                    'price' => $price,
                                    //defaults for import
                                    'manufacturer_id' => $manufacturer_id,
                                    'minimum' => 1,
                                    'subtract' => false,
                                    'shipping' => true,
                                    'points' => 0,
                                    'date_available' => nowMySQLTimestamp(),
                                    'weight_class_id' => 1, //Kilogram
                                    'length' => 0,
                                    'width' => 0,
                                    'height' => 0,
                                    'length_class_id' => 1, //Santimeter
                                    'stock_status_id' => 7, //Available
                                    'tax_class_id' => 0,
                                    'status' => $category_id === 0 ? false : true,
                                    'noindex' => true,
                                    'sort_order' => 1
                                );

                                //Add product to default store
                                $product['product_store'] = array($store_id);

                                //Add product to parent category
                                if ($parent_category_id > 0) $product['product_category'] = array($parent_category_id);

                                //Add seo urls
                                $seo_data = array();
                                foreach ($seo as $item) {
                                    $seo_name = getSeoUrlByName($name);
                                    $seo_data[$item['language_id']] = "{$seo_name}_{$item['prefix']}";
                                }

                                $product['product_seo_url'][$store_id] = $seo_data;

                                //try to find existing product by name & model and update by id if exists
                                $filter = array(
                                    'filter_model' => $model,
                                    'filter_name' => $name
                                );

                                $results = $this->model_catalog_product->getProducts($filter);
                                if (!empty($results)) {
                                    $count = count($results);
                                } else {
                                    $count = 0;
                                }

                                //we successful find product - Update
                                if ($count === 1) {
                                    $product_id = $results[0]['product_id'];

                                    //Update here
                                    $this->model_catalog_product->updateProductData($product_id, $product);

                                    echo $msg = "{$sheet_name} : {$line}) Product exists - Update - {$name}: {$product_id}<br>";
                                    $log->write($msg);

                                } elseif ($count === 0) {
                                    //not exists - create

                                    //fill all languages name!!! ONLY FOR INSERT
                                    foreach ($languages as $language) {
                                        $product['product_description'][$language['language_id']] = array(
                                            'name'             => $name,
                                            'description'      => '',
                                            'meta_title'       => '',
                                            'meta_h1'	       => '',
                                            'meta_description' => '',
                                            'meta_keyword'     => '',
                                            'tag'              => ''
                                        );
                                    }

                                    //Insert here
                                    $product_id = $this->model_catalog_product->addProduct($product);

                                    //TODO: add to log
                                    echo $msg = "{$sheet_name} : {$line}) Added product - {$name}: {$product_id}<br>";
                                    $log->write($msg);

                                } else {

                                    //We find more than > 1 product by model and name, what to do ?!?!?!?! O_o OR NOTHING
                                    echo $msg = '--------------------------------------------------------------------\n';
                                    echo $msg .= "{$sheet_name} : {$line}) Error: We find more than 1 concurrence - skipped for next Product - model: {$model}, name: {$name}<br>\n";
                                    $msg .= print_r($results, true);
                                    echo $msg .= '--------------------------------------------------------------------<br><br>\n';
                                    $log->write($msg);
                                }

                            //category or subcategory if was set parent category
                            } elseif (empty($model) && !empty($name) && empty($weight) &&  empty($price)) { //set category - feature of excel file

                                //try to find category and get id
                                $filter = array(
                                    'filter_name' => $name
                                );

                                $results = $this->model_catalog_category->getCategories($filter);
                                if (!empty($results)) {
                                    $count = count($results);
                                } else {
                                    $count = 0;
                                }

                                $category = array(
                                    'parent_id' => $parent_category_id,
                                    'top' => false,
                                    'column' => 0,
                                    'sort_order' => 1,
                                    'status' => true,
                                    'noindex' => false,
                                );

                                if ($count === 1) {

                                    //we find category - just get id to import products
                                    $category_id = $results[0]['category_id'];

                                    //TODO: add to log
                                    echo $msg = "{$sheet_name} : {$line}) Category exists - {$name}: {$category_id}<br>";
                                    $log->write($msg);

                                } elseif ($count === 0) {

                                    //create if not exists and get id

                                    foreach ($languages as $language) {
                                        $category['category_description'][$language['language_id']] = array(
                                            'name'             => $name,
                                            'meta_title'       => '',
                                            'meta_h1'      	   => '',
                                            'meta_description' => '',
                                            'meta_keyword'     => '',
                                            'description'      => ''
                                        );
                                    }

                                    //Add category to default store
                                    $category['category_store'] = array($store_id);

                                    //Add seo urls
                                    $seo_data = array();
                                    foreach ($seo as $item) {
                                        $seo_name = getSeoUrlByName($name);
                                        $seo_data[$item['language_id']] = "{$seo_name}_{$item['prefix']}";
                                    }

                                    $category['category_seo_url'][$store_id] = $seo_data;

                                    $category_id = $this->model_catalog_category->addCategory($category);

                                    //TODO: Add to log
                                    echo $msg = "{$sheet_name} :{$line}) Added category - {$name}: {$category_id}<br>";
                                    $log->write($msg);
                                }

                            } else {
                                echo $msg = "{$sheet_name} : {$line}) Skipped <br>";
                                $log->write($msg);
                            }
                        }
                        $line++;
                    }

                } else {
                    echo $msg = "Error: Excel file header validation failed!<br><br>";
                    $log->write($msg);
                }


//            }

            $finish = time();

            echo 'Algorithm Finished In: ' . ($finish - $start) . ' seconds.';

            $product_list = $this->getUrl()->link('catalog/product', 'user_token=' . $this->getSession()->data['user_token'], true);
            echo "<br><br><a href='{$product_list}' class='btn btn-primary'>Go back to Product List</a><br><br>";

            ini_set('display_errors', 0);
        }
    }

    public function clearProducts()
    {
        if ($this->getUser()->isLogged() && $this->getUser()->getGroupId() == \Support\User::ADMIN_GROUP_ID) {
            $this->getLoader()->model('catalog/product');
            $products = $this->model_catalog_product->getProducts(array(
                'start' => 0,
                'limit' => 1000
            ));

            foreach ($products as $product) {
                $this->model_catalog_product->deleteProduct($product['product_id']);
            }

            $this->getResponse()->redirect($this->getUrl()->link('catalog/product', 'user_token=' . $this->getSession()->data['user_token'], true));
        }
    }

    public function clearCategories()
    {
        if ($this->getUser()->isLogged() && $this->getUser()->getGroupId() == \Support\User::ADMIN_GROUP_ID) {

            $this->getLoader()->model('catalog/category');

            $categories = $this->model_catalog_category->getCategories(array(
                'start' => 0,
                'limit' => 1000
            ));

            foreach ($categories as $category) {
                $this->model_catalog_category->deleteCategory($category['category_id']);
            }

            $this->getResponse()->redirect($this->getUrl()->link('catalog/product', 'user_token=' . $this->getSession()->data['user_token'], true));
        }
    }


}

/*
 * TODO: Set AUTO_INCREMENT values to last ids in categories, products...
 *
 * Clear SEO urls
 * SELECT * FROM `oc_seo_url` WHERE `seo_url_id` >= 12972
 *
 *
 */