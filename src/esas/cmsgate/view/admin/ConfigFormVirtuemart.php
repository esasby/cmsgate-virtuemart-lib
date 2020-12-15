<?php
/*
* @info     Платёжный модуль Hutkigrosh для JoomShopping
* @package  hutkigrosh
* @author   esas.by
* @license  GNU/GPL
*/

namespace esas\cmsgate\view\admin;

use esas\cmsgate\CmsConnectorVirtuemart;
use esas\cmsgate\utils\XMLUtils;
use esas\cmsgate\view\admin\fields\ConfigField;
use esas\cmsgate\view\admin\fields\ConfigFieldCheckbox;
use esas\cmsgate\view\admin\fields\ConfigFieldList;
use esas\cmsgate\view\admin\fields\ConfigFieldPassword;
use esas\cmsgate\view\admin\fields\ConfigFieldRichtext;
use esas\cmsgate\view\admin\fields\ConfigFieldTextarea;
use esas\cmsgate\view\admin\fields\ListOption;
use SimpleXMLElement;

class ConfigFormVirtuemart extends ConfigFormArray
{
    private $orderStatuses;

    /**
     * ConfigFormVirtuemart constructor.
     */
    public function __construct($formKey, $managedFields)
    {
        parent::__construct($formKey, $managedFields);
        $statsList = CmsConnectorVirtuemart::getInstance()->getModuleModel()->getOrderStatuses();
        foreach ($statsList as $key => $value) {
            $this->orderStatuses[] = new ListOption($key, $value);
        }
    }

    public function generate()
    {
        $fieldSetElement = new SimpleXMLElement("<fieldset />");
        $fieldSetElement->addAttribute("name", $this->getFormKey());
        foreach (parent::generate() as $key => $value) {
            XMLUtils::simpleXMLAppend($fieldSetElement, $value);
        }
        return $fieldSetElement;
    }


    /**
     * @return ListOption[]
     */
    public function createStatusListOptions()
    {
        return $this->orderStatuses;
    }

    private static function createFieldElement(ConfigField $configField, $addDefaultAttribute = false, $addRequiredAttribute = false)
    {
        $element = new SimpleXMLElement("<field />");
        $element->addAttribute("name", $configField->getKey());
        $element->addAttribute("label", $configField->getName());
        $element->addAttribute("description", $configField->getDescription());
        if ($addDefaultAttribute && $configField->hasDefault())
            $element->addAttribute("default", $configField->getDefault());
        if ($addRequiredAttribute && $configField->isRequired())
            $element->addAttribute("required ", "1");
        return $element;
    }


    function generateTextField(ConfigField $configField)
    {
        $element = self::createFieldElement($configField, true, true);
        $element->addAttribute("type", "text");
        return $element;
    }

    function generateTextAreaField(ConfigFieldTextarea $configField)
    {
        $element = self::createFieldElement($configField);
        $element->addAttribute("type", "textarea");
        $element->addAttribute("rows", $configField->getRows());
        $element->addAttribute("cols", $configField->getCols());
        return $element;
    }

    function generateRichtextField(ConfigFieldRichtext $configField)
    {
        $element = self::createFieldElement($configField);
        $element->addAttribute("type", "editor");
//        not supported
//        $element->addAttribute("rows", $configField->getRows());
//        $element->addAttribute("cols", $configField->getCols());
        return $element;
    }


    public function generatePasswordField(ConfigFieldPassword $configField)
    {
        $element = self::createFieldElement($configField);
        $element->addAttribute("type", "password");
        return $element;
    }


    function generateCheckboxField(ConfigFieldCheckbox $configField)
    {
        $element = self::createFieldElement($configField);
        $element->addAttribute("type", "checkbox");
        $element->addAttribute("value", "1");
        if ($configField->hasDefault())
            $element->addAttribute("checked ", $configField->getDefault() ? "1" : "0");
        return $element;
    }

    function generateListField(ConfigFieldList $configField)
    {
        $element = self::createFieldElement($configField);
        $element->addAttribute("type", "list");
        foreach ($configField->getOptions() as $listOption) {
            $option = $element->addChild("option", $listOption->getName());
            $option->addAttribute("value", $listOption->getValue());
        }
        return $element;
    }
}