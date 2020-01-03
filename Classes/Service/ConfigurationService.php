<?php
namespace Lemming\Vips\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ConfigurationService {

    public static function getFileExtensions() {
        return self::getExtensionConfigurationKey('fileExtensions');
    }

    protected static function getExtensionConfigurationKey($key) {
        if (version_compare(VersionNumberUtility::getNumericTypo3Version(), 9, '<')) {
            $configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['vips']);
        } else {
            $configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('vips');
        }

        return $configuration[$key];
    }
}