<?php
namespace Lemming\Vips\Resource\Processing;

use TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class ImageProcessor extends LocalImageProcessor
{
    protected function getHelperByTaskName($taskName)
    {
        $isVersion10 = version_compare(VersionNumberUtility::getNumericTypo3Version(), 10, '>=');

        switch ($taskName) {
            case 'Preview':
                $helper = $isVersion10 ? GeneralUtility::makeInstance(PreviewHelper::class) : GeneralUtility::makeInstance(PreviewHelper::class, $this);
                break;
            case 'CropScaleMask':
                $helper = $isVersion10 ? GeneralUtility::makeInstance(CropScaleMaskHelper::class) : GeneralUtility::makeInstance(CropScaleMaskHelper::class, $this);
                break;
            default:
                throw new \InvalidArgumentException('Cannot find helper for task name: "' . $taskName . '"',
                    1353401352);
        }

        return $helper;
    }
}