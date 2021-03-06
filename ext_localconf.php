<?php

if (extension_loaded('vips')) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor::class] = [
        'className' => \Lemming\Vips\Resource\Processing\ImageProcessor::class
    ];

    if (!version_compare(\TYPO3\CMS\Core\Utility\VersionNumberUtility::getNumericTypo3Version(), 9, '<')) {
        $isComposerMode = \TYPO3\CMS\Core\Core\Environment::isComposerMode();
    } else {
        $isComposerMode = \TYPO3\CMS\Core\Core\Bootstrap::usesComposerClassLoading();
    }
    if (!$isComposerMode) {
        $autoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('vips') . 'vendor/autoload.php';
        require_once($autoloadFile);
    }
}

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['vips'][] =
        \Lemming\Vips\Report\Report::class;
}
