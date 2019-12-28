<?php
namespace Lemming\Vips\Resource\Processing;

use Jcupitt\Vips\Image;
use Lemming\Vips\Service\ConfigurationService;
use TYPO3\CMS\Core\Log\LogManager;
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
        /** @var $logger \TYPO3\CMS\Core\Log\Logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        if (!GeneralUtility::inList(ConfigurationService::getFileExtensions(), $file->getExtension())) {
            $logger->info(sprintf('Unsupported: %s Fallback to TYPO3',$file->getPublicUrl()), $configuration);
            return parent::generatePreviewFromFile($file, $configuration, $targetFilePath);
        }

        $originalFile = $file->getForLocalProcessing(false);
        try {
            $image = Image::thumbnail(
                $originalFile,
                $configuration['width'],
                [
                    'height' => $configuration['height']
                ]
            );

            $image->writeToFile($targetFilePath,
                [
                    "Q" => MathUtility::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'], 10, 100,
                        85),
                    "strip" => true
                ]
            );

            $logger->info(sprintf('Succesfully processed %s', $originalFile), $configuration);

            $result = [
                'width' => $image->width,
                'height' => $image->height,
                'filePath' => $targetFilePath
            ];

            return $result;
        } catch (\Throwable $e) {
            $logger->critical(sprintf('Failed to process %s. Got "%s" in %s:%d', $originalFile, $e->getMessage(),
                $e->getFile(), $e->getLine()), $configuration);
        }
    }
}
