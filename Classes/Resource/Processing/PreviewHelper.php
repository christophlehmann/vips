<?php
namespace Lemming\Vips\Resource\Processing;

use Jcupitt\Vips\Image;
use Lemming\Vips\Service\ConfigurationService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Processing\LocalPreviewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class PreviewHelper extends LocalPreviewHelper
{
    /**
     * @inheritDoc
     *
     * @throws \Jcupitt\Vips\Exception
     */
    protected function generatePreviewFromFile(File $file, array $configuration, $targetFilePath)
    {
        if (!GeneralUtility::inList(ConfigurationService::getFileExtensions(), $file->getExtension())) {
            return parent::generatePreviewFromFile($file, $configuration, $targetFilePath);
        }

        $originalFile = $file->getForLocalProcessing(false);

        $image = Image::thumbnail(
            $originalFile,
            $configuration['width'],
            [
                'height' => $configuration['height']
            ]
        );

        $image->writeToFile($targetFilePath,
            [
                "Q" => MathUtility::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'], 10, 100, 85),
                "strip" => true
            ]
        );

        $result = [
            'width' => $image->width,
            'height' => $image->height,
            'filePath' => $targetFilePath
        ];

        return $result;
    }
}
