<?php
namespace Lemming\Vips\Service;

use Lemming\Vips\Compatibility\Compatibility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService {

    public static function getFileExtensions() {
        return self::getExtensionConfigurationKey('fileExtensions');
    }

    protected static function getExtensionConfigurationKey($key) {
        if (Compatibility::isVersion8()) {
            $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['vips']);
        } else {
            $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('vips');
        }

        return $configuration[$key];
    }
}