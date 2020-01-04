# TYPO3 Extension: Vips

Faster and less memory hungry thumbnail generation for TYPO3 with [libvips](https://libvips.github.io/libvips/)

Here is a speed and memory comparison: https://github.com/libvips/libvips/wiki/Speed-and-memory-use

## Prerequisites

The [PHP module vips](https://github.com/libvips/php-vips-ext) with version 8.8+ needs to be present on the server. The versions 8.5+ may work, but are not tested. 

@jcupitt provides some [Dockerfiles](https://github.com/jcupitt/docker-builds) which may help you to get a newer version.

The module can be installed with `pecl install vips`

## Installation

`composer require christophlehmann/vips`

## Configuration

By default the file types `jpg,jpeg,png,webp,pdf` are handled. Other types are handled through TYPO3. This list can be
configured in EM. If you have problems with certain file types just deactivate them.

## Implemented functionality

* [x] Crop images
* [x] Scale images
* [ ] Mask images (currently done with TYPO3's default image processor)

## Debugging

Add the logging configuration to `typo3conf/AdditionalConfiguration.php`

```php
<?php
$GLOBALS['TYPO3_CONF_VARS']['LOG']['Lemming']['Vips']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
        \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
            'logFileInfix' => 'vips'
        ]
    ]
];

```
Then create a thumbnail and check the log `typo3temp/var/log/typo3_vips_ea8bea6399.log`

## Contribution

Contributions are very welcome! The extension is managed at [Github](https://github.com/christophlehmann/vips)
