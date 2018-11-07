<?php

/**
 * @author     Luca Ioffredo
 * @copyright  Luca Ioffredo
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$require = array(
//    'classes/assistanceflashcard.php',
);

foreach ($require as $item) {
    require_once(_PS_MODULE_DIR_ . 'assistanceflashcard/' . $item);
}

class Assistanceflashcard extends Module {

    protected $config_form = false;

    public function __construct() {
        $this->name = 'assistanceflashcard';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Luca Ioffredo';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Assistance FlashCard Popup');
        $this->description = $this->l('Add a flashcard popup with a message about assistance. Created by Luca Ioffredo, alias Latios93');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install() {
        return parent::install() && $this->registerHook($this->getHooks());
    }

    public function uninstall() {
        return parent::uninstall() && $this->uninstallHooks();
    }

    public function getHooks() {
        return array(
            'displayHeader',
            'displayFooter',
        );
    }

    private function uninstallHooks() {
        $res = true;
        foreach ($this->getHooks() as $hook) {
            $res = $res && $this->unregisterHook($hook);
        }
        return $res;
    }

    public function hookHeader($params) {
        if (self::getVersion() != "1.6") {
            $this->context->controller->registerStylesheet(
                    'assistanceflashcard-css', 'modules/' . $this->name . '/views/css/front/assistanceflashcard.css'
            );
            $this->context->controller->registerJavascript(
                    'assistanceflashcard-js', 'modules/' . $this->name . '/views/js/front/assistanceflashcard.js', array(
                'position' => 'bottom',
                'inline' => false,
                'priority' => 1000,
                    )
            );
        } else {
            $this->context->controller->addCSS(($this->_path) . '/views/css/front/assistanceflashcard.css', 'all');
            $this->context->controller->addJS(($this->_path) . '/views/js/front/assistanceflashcard.js');
        }
    }

    public static function getVersion() {
        return Tools::substr(_PS_VERSION_, 0, 3);
    }

    public function hookDisplayFooter($params) {
        $this->context->smarty->assign(array(
            'url_assistance_flash_card' => Configuration::get('URL_ASSISTANCEFLASHCARD')
        ));
        return $this->display(__FILE__, 'flashcard.tpl');
    }

    public function getContent() {

        $this->_header = '<div class="panel"><div class="alert alert-info" style="clear: both;">Here, you can set the URL of the your assistance page.</div></div>';
        $this->_html = '';
        $this->_postProcess();
        return $this->_header . $this->displayForm() . $this->_html;
    }

    public function displayForm() {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $fields_form = array(array());
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Url Facebook page'),
                    'name' => 'name_assistanceflashcard',
                    'class' => 'lg',
                    'required' => true,
                    'desc' => $this->l('Url of the page on facebook.')
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );
        $helper = new HelperForm();
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit' . $this->name;
        $helper->fields_value = $this->getConfigFormValues();
        return $helper->generateForm($fields_form);
    }

    public function getConfigFormValues() {
        return array(
            'name_assistanceflashcard' => Configuration::get('URL_ASSISTANCEFLASHCARD')
        );
    }

    protected function _postProcess() {
        if (Tools::isSubmit('submit' . $this->name)) {
            Configuration::updateValue('URL_ASSISTANCEFLASHCARD', Tools::getValue('name_assistanceflashcard'));
            $this->_html .= '<div class="conf confirm alert alert-success">' . $this->l('Settings updated') . '</div>';
        }
    }

}
