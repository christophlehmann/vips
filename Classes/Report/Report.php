<?php
namespace Lemming\Vips\Report;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Reports\StatusProviderInterface;

class Report implements StatusProviderInterface
{
    public function getStatus()
    {
        $isLoaded = extension_loaded('vips');
        $versionSupported = $isLoaded && version_compare(vips_version(), '8.8', '>=');
        $status['vips'] = GeneralUtility::makeInstance(ObjectManager::class)->get(
            Status::class,
            'PHP Module vips',
            $isLoaded ? 'Version ' . vips_version() : 'Not loaded',
            '',
            $isLoaded && $versionSupported ? Status::OK : Status::ERROR
        );

        return $status;
    }
}
