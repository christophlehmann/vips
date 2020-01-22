<?php
namespace Lemming\Vips\Resource\Processing;

use Jcupitt\Vips\Image;
use Lemming\Vips\Service\ConfigurationService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Imaging\GifBuilder;

class CropScaleMaskHelper extends LocalCropScaleMaskHelper
{
    public function process(TaskInterface $task)
    {
        /** @var $logger \TYPO3\CMS\Core\Log\Logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $targetFile = $task->getTargetFile();
        $fileExtensionIsSupported = GeneralUtility::inList(ConfigurationService::getFileExtensions(),
            $targetFile->getExtension());
        $configuration = $targetFile->getProcessingConfiguration();
        $isMaskTask = isset($configuration['maskImages']) && is_array($configuration['maskImages']);

        if (!$fileExtensionIsSupported || $isMaskTask) {
            $logger->info(sprintf('Unsupported: %s Fallback to TYPO3', $task->getSourceFile()->getPublicUrl()),
                $configuration);
            return parent::process($task);
        }

        try {
            $sourceFile = $task->getSourceFile();
            $targetFileName = $this->getFilenameForImageCropScaleMask($task);
            $originalFileName = $sourceFile->getForLocalProcessing(false);

            if (empty($configuration['fileExtension'])) {
                $configuration['fileExtension'] = $task->getTargetFileExtension();
            }

            $gifBuilder = GeneralUtility::makeInstance(GifBuilder::class);
            $options = $this->getConfigurationForImageCropScaleMask($targetFile, $gifBuilder);
            $graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
            $info = $graphicalFunctions->getImageDimensions($originalFileName);
            $data = $graphicalFunctions->getImageScale(
                $info,
                $configuration['width'],
                $configuration['height'],
                $options
            );

            $cropArea = $this->getCropArea($configuration);
            if ($cropArea) {
                $thumbnailFactor = $cropArea[2] / $data[0];

                // Do not upscale
                if ($thumbnailFactor > 1) {
                    $thumbnailWidth = $info[0] / $thumbnailFactor;
                    $thumbnailHeight = $info[1] / $thumbnailFactor;
                    $cropArea = [
                        $cropArea[0] / $thumbnailFactor,
                        $cropArea[1] / $thumbnailFactor,
                        $data[0],
                        $data[1],
                    ];
                    $logger->debug('thumbnail croparea ' . $originalFileName, [$thumbnailWidth, $thumbnailHeight, $cropArea]);
                    $image = Image::thumbnail(
                        $originalFileName,
                        $thumbnailWidth,
                        ['height' => $thumbnailHeight]
                    );
                } else {
                    $image = Image::newFromFile($originalFileName);
                }

                $logger->debug('crop croparea ' . $originalFileName, [$cropArea, $image->width, $image->height]);
                $image = $image->crop($cropArea[0], $cropArea[1], $cropArea[2], $cropArea[3]);
            } else {
                $logger->debug('thumbnail ' . $originalFileName, $data);
                $image = Image::thumbnail(
                    $originalFileName,
                    $data[0],
                    ['height' => $data[1]]
                );

                if ($data['crs']) {
                    if (!$data['origW']) {
                        $data['origW'] = $data[0];
                    }
                    if (!$data['origH']) {
                        $data['origH'] = $data[1];
                    }

                    // Calculate the middle
                    $offsetX = (int)(($data[0] - $data['origW']) * ($data['cropH'] + 100) / 200);
                    $offsetY = (int)(($data[1] - $data['origH']) * ($data['cropV'] + 100) / 200);

                    $logger->debug('crop crs ' . $originalFileName, [$offsetX, $offsetY, $data]);
                    $image = $image->crop($offsetX, $offsetY, $data['origW'], $data['origH']);
                }
            }

            $temporaryFileName = self::getTemporaryFileName($targetFileName);
            $this->saveImage($image, $temporaryFileName);

            $result = [
                'width' => $image->width,
                'height' => $image->height,
                'filePath' => $temporaryFileName
            ];
            $logger->debug('result ' . $originalFileName, $result);

            return $result;
        } catch (\Throwable $e) {
            $logger->critical(sprintf('Failed to process %s. Got "%s" in %s:%d', $originalFileName, $e->getMessage(),
                $e->getFile(), $e->getLine()), $configuration);
        }
    }

    protected function saveImage($image, $temporaryFileName)
    {
        $quality = MathUtility::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'], 10, 100, 85);
        $image->writeToFile($temporaryFileName,
            [
                "Q" => $quality,
                "strip" => true
            ]
        );
    }

    /**
     * @param array $configuration
     * @return array|null [$offsetLeft, $offsetTop, $width, $height]
     */
    protected function getCropArea($configuration)
    {
        if (!empty($configuration['crop'])) {
            // check if it is a json object as done in parent
            $cropArea = json_decode($configuration['crop']);
            if ($cropArea) {
                $crop = implode(',',
                    [(int)$cropArea->x, (int)$cropArea->y, (int)$cropArea->width, (int)$cropArea->height]);
            } else {
                $crop = $configuration['crop'];
            }
            $cropArea = explode(',', $crop, 4);

            return $cropArea;
        }
    }

    public static function getTemporaryFileName($fileName)
    {
        if (version_compare(VersionNumberUtility::getNumericTypo3Version(), 9, '<')) {
            $temporaryFileName = PATH_site . 'typo3temp/' . $fileName;
        } else {
            $temporaryFileName = Environment::getPublicPath() . '/typo3temp/' . $fileName;
        }

        return $temporaryFileName;
    }
}
