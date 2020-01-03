<?php
namespace Lemming\Vips\Resource\Processing;

use Jcupitt\Vips\Image;
use Lemming\Vips\Service\ConfigurationService;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class CropScaleMaskHelper extends LocalCropScaleMaskHelper
{
    protected $configuration = [];

    /**
     * @inheritDoc
     */
    public function process(TaskInterface $task)
    {
        $targetFile = $task->getTargetFile();
        $fileExtensionIsSupported = GeneralUtility::inList(ConfigurationService::getFileExtensions(),
            $targetFile->getExtension());
        $this->configuration = $targetFile->getProcessingConfiguration();
        $isMaskTask = isset($this->configuration['maskImages']) && is_array($this->configuration['maskImages']);

        /** @var $logger \TYPO3\CMS\Core\Log\Logger */
        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        if (!$fileExtensionIsSupported || $isMaskTask) {
            $logger->info(sprintf('Unsupported: %s Fallback to TYPO3', $task->getSourceFile()->getPublicUrl()),
                $this->configuration);
            return parent::process($task);
        }

        try {
            $sourceFile = $task->getSourceFile();
            $targetFileName = $this->getFilenameForImageCropScaleMask($task);
            $originalFileName = $sourceFile->getForLocalProcessing(false);

            if (empty($this->configuration['fileExtension'])) {
                $this->configuration['fileExtension'] = $task->getTargetFileExtension();
            }

            $image = Image::newFromFile($originalFileName);
            if (!empty($this->configuration['crop'])) {
                $image = $this->cropImage($image);
            }

            $image = $this->thumbnail($image, $this->configuration['fileExtension']);
            $quality = MathUtility::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'], 10, 100, 85);
            $image->writeToFile($targetFileName,
                [
                    "Q" => $quality,
                    "strip" => true
                ]
            );

            $logger->info(sprintf('Succesfully processed %s', $originalFileName), $this->configuration);

            $result = [
                'width' => $image->width,
                'height' => $image->height,
                'filePath' => $targetFileName
            ];

            return $result;
        } catch (\Throwable $e) {
            $logger->critical(sprintf('Failed to process %s. Got "%s" in %s:%d', $originalFileName, $e->getMessage(),
                $e->getFile(), $e->getLine()), $this->configuration);
        }
    }

    protected function cropImage($image)
    {
        // check if it is a json object as done in parent
        $cropData = json_decode($this->configuration['crop']);
        if ($cropData) {
            $crop = implode(',', [(int)$cropData->x, (int)$cropData->y, (int)$cropData->width, (int)$cropData->height]);
        } else {
            $crop = $this->configuration['crop'];
        }

        list($offsetLeft, $offsetTop, $newWidth, $newHeight) = explode(',', $crop, 4);
        $image = $image->crop($offsetLeft, $offsetTop, $newWidth, $newHeight);

        return $image;
    }

    protected function thumbnail($image, $suffix)
    {
        $width = $this->configuration['width'] ?? $this->configuration['maxWidth'] ?? $image->width;
        $height = $this->configuration['height'] ?? $this->configuration['maxHeight'] ?? $image->height;

        $buffer = $image->writeToBuffer('.' . $this->configuration['fileExtension']);
        $image = \Jcupitt\Vips\Image::thumbnail_buffer(
            $buffer,
            (integer)$width,
            [
                'height' => $height
            ]
        );

        return $image;
    }
}
