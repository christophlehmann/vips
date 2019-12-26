<?php
namespace Lemming\Vips\Resource\Processing;

use Jcupitt\Vips\Image;
use Lemming\Vips\Service\ConfigurationService;
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
        $fileExtensionIsSupported = GeneralUtility::inList(ConfigurationService::getFileExtensions(), $targetFile->getExtension());
        $this->configuration = $targetFile->getProcessingConfiguration();
        $isMaskTask = is_array($this->configuration['maskImages']);

        if (!$fileExtensionIsSupported || $isMaskTask) {
            return parent::process($task);
        }

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
        $image = $this->thumbnail($image);
        $image->writeToFile($targetFileName,
            [
                "Q" => MathUtility::forceIntegerInRange($GLOBALS['TYPO3_CONF_VARS']['GFX']['jpg_quality'], 10, 100, 85),
                "strip" => true
            ]
        );

        $result = [
            'width' => $image->width,
            'height' => $image->height,
            'filePath' => $targetFileName
        ];

        return $result;
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

    protected function thumbnail($image)
    {
        $width = $this->configuration['width'] ?? $this->configuration['maxWidth'] ?? $image->width;
        $height = $this->configuration['height'] ?? $this->configuration['maxHeight'] ?? $image->height;

        $image = $image->thumbnail_image(
            $width,
            [
                'height' => $height
            ]
        );

        return $image;
    }
}
