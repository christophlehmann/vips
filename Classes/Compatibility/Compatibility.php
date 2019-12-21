<?php
namespace Lemming\Vips\Compatibility;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class Compatibility
{
    public static function isVersion8()
    {
        return version_compare(VersionNumberUtility::getNumericTypo3Version(), 9, '<');
    }
}