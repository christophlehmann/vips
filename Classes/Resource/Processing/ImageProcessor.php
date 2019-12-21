<?php
namespace Lemming\Vips\Resource\Processing;

use Lemming\Vips\Compatibility\Compatibility;
use Lemming\Vips\Service\ConfigurationService;
use TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImageProcessor extends LocalImageProcessor
{
    public function canProcessTask(TaskInterface $task)
    {
        $fileExtension = $task->getTargetFile()->getExtension();
        $fileExtensionIsSupported = GeneralUtility::inList(ConfigurationService::getFileExtensions(), $fileExtension);
        $configuration = $task->getTargetFile()->getProcessingConfiguration();
        $isNoMaskTask = !is_array($configuration['maskImages']);

        $canProcessTask = parent::canProcessTask($task) && $fileExtensionIsSupported && $isNoMaskTask;
        return $canProcessTask;
    }

    protected function getHelperByTaskName($taskName)
    {

        switch ($taskName) {
            case 'Preview':
                $helper = Compatibility::isVersion8() ? GeneralUtility::makeInstance(PreviewHelper::class, $this) : GeneralUtility::makeInstance(PreviewHelper::class);
                break;
            case 'CropScaleMask':
                $helper = Compatibility::isVersion8() ? GeneralUtility::makeInstance(CropScaleMaskHelper::class, $this) : GeneralUtility::makeInstance(CropScaleMaskHelper::class);
                break;
            default:
                throw new \InvalidArgumentException('Cannot find helper for task name: "' . $taskName . '"',
                    1353401352);
        }

        return $helper;
    }
}