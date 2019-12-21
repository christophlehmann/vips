<?php
namespace Lemming\Vips\Resource\Processing;

use Lemming\Vips\Compatibility\Compatibility;
use TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImageProcessor extends LocalImageProcessor
{
    protected function getHelperByTaskName($taskName)
    {
        switch ($taskName) {
            case 'Preview':
                $helper = Compatibility::isVersion10() ? GeneralUtility::makeInstance(PreviewHelper::class) : GeneralUtility::makeInstance(PreviewHelper::class, $this);
                break;
            case 'CropScaleMask':
                $helper = Compatibility::isVersion10() ? GeneralUtility::makeInstance(CropScaleMaskHelper::class) : GeneralUtility::makeInstance(CropScaleMaskHelper::class, $this);
                break;
            default:
                throw new \InvalidArgumentException('Cannot find helper for task name: "' . $taskName . '"',
                    1353401352);
        }

        return $helper;
    }
}