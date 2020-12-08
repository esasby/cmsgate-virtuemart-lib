<?php
/**
 * Created by IntelliJ IDEA.
 * User: nikit
 * Date: 15.07.2019
 * Time: 13:14
 */

namespace esas\cmsgate;


use esas\cmsgate\utils\OpencartVersion;
use Exception;

class ConfigStorageVirtuemart extends ConfigStorageCms
{
    private $configuration = array();

    /**
     * ConfigurationWrapperOpencart constructor.
     * @param $config
     */
    public function __construct()
    {
        parent::__construct();
        $this->configuration = CmsConnectorVirtuemart::getInstance()->getModuleModel()->getModuleConfig();
    }

    /**
     * @param $key
     * @return string
     * @throws Exception
     */
    public function getConfig($key)
    {
        if (array_key_exists($key, $this->configuration))
            return $this->configuration[$key];
        else
            return null;
    }

    /**
     * @param $cmsConfigValue
     * @return bool
     * @throws Exception
     */
    public function convertToBoolean($cmsConfigValue)
    {
        return strtolower($cmsConfigValue) == '1';
    }

    public function createCmsRelatedKey($key)
    {
        return $key;
    }

    /**
     * Сохранение значения свойства в харнилища настроек конкретной CMS.
     *
     * @param string $key
     * @throws Exception
     */
    public function saveConfig($key, $value)
    {
        //not implemented
    }
}