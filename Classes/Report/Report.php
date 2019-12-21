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
        $status['vips'] = GeneralUtility::makeInstance(ObjectManager::class)->get(
            Status::class,
            'PHP Module vips',
            $isLoaded ? 'Version ' . vips_version() : 'Not loaded',
            '',
            $isLoaded ? Status::OK : Status::ERROR
        );

        return $status;
    }
}
