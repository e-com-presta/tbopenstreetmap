<?php

if (!defined('_TB_VERSION_')) {
    exit;
}

class TbOpenStreetMap extends Module
{
    public function __construct()
    {
        $this->name = 'tbopenstreetmap';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'E-Com';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('OpenStreetMap');
        $this->description = $this->l('Generate a OpenStreetMap for all your stores.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';
    }

    public function install()
    {
        return parent::install() &&
            $this->registerHook('displayStoreMap') &&
            $this->registerHook('header');
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            Configuration::deleteByName('TBOPENSTREETMAP_IMG');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitSettings')) {
            $shopImg = Tools::getValue('TBOPENSTREETMAP_IMG');
            if ($shopImg != 0 && $shopImg != 1) {
                $output .= $this->displayError($this->l('Invalid choice.'));
            } else {
                Configuration::updateValue('TBOPENSTREETMAP_IMG', (int) ($shopImg));
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $formFields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display shop image'),
                        'name' => 'TBOPENSTREETMAP_IMG',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save')
                ],
            ],
        ];
        $controller = $this->context->controller;
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];
        return $helper->generateForm([$formFields]);
    }

    public function getConfigFieldsValues()
    {
        return [
            'TBOPENSTREETMAP_IMG' => (bool) Tools::getValue('TBOPENSTREETMAP_IMG', Configuration::get('TBOPENSTREETMAP_IMG')),
        ];
    }

    public function hookHeader()
    {
        if ($this->context->controller instanceof StoresController) {
            $this->context->controller->addCSS($this->_path.'views/css/leaflet.css', 'all');
            $this->context->controller->addJS($this->_path.'views/js/leaflet.js');
            $this->context->controller->addJS($this->_path.'views/js/leaflet.fullscreen.min.js');
            $this->context->controller->addJS($this->_path.'views/js/Control.Geocoder.js');
        }
    }

    public function hookDisplayStoreMap()
    {
        $stores = Db::getInstance()->executeS('
            SELECT s.*, cl.name country, st.iso_code state
            FROM '._DB_PREFIX_.'store s
            '.Shop::addSqlAssociation('store', 's').'
            LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = s.id_country)
            LEFT JOIN '._DB_PREFIX_.'state st ON (st.id_state = s.id_state)
            WHERE s.active = 1
            AND cl.id_lang = '.(int) $this->context->language->id
        );
        foreach ($stores as &$store) {
            $store['has_picture'] = file_exists(_PS_STORE_IMG_DIR_.(int) $store['id_store'].'.jpg');
            if ($workingHours = $this->renderStoreWorkingHours($store)) {
                $store['working_hours'] = $workingHours;
            }
        }
        $this->smarty->assign(
            [
                'center_lon' => (float) Configuration::get('PS_STORES_CENTER_LONG'),
                'center_lat' => (float) Configuration::get('PS_STORES_CENTER_LAT'),
                'store_icon' => Configuration::get('PS_STORES_ICON'),
                'show_image' => Configuration::get('TBOPENSTREETMAP_IMG'),
                'mediumSize' => Image::getSize(ImageType::getFormatedName('medium')),
                'stores' => $stores
            ]
        );
        return $this->display(__FILE__, 'tbopenstreetmap.tpl');
    }

    public function renderStoreWorkingHours($store)
    {
        $days[1] = 'Monday';
        $days[2] = 'Tuesday';
        $days[3] = 'Wednesday';
        $days[4] = 'Thursday';
        $days[5] = 'Friday';
        $days[6] = 'Saturday';
        $days[7] = 'Sunday';
        $daysDatas = [];
        $hours = [];
        if ($store['hours']) {
            $hours = json_decode($store['hours'], true);
            if (!$hours) {
                $hours = Tools::unSerialize($store['hours']);
            }
            if (is_array($hours)) {
                $hours = array_filter($hours);
            }
        }
        if (!empty($hours)) {
            for ($i = 1; $i < 8; $i++) {
                if (isset($hours[(int) $i - 1])) {
                    $hoursDatas = [];
                    $hoursDatas['hours'] = $hours[(int) $i - 1];
                    $hoursDatas['day'] = $days[$i];
                    $daysDatas[] = $hoursDatas;
                }
            }
            return $daysDatas;
        }
        return false;
    }
}
