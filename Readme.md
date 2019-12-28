# TYPO3 Extension: Vips

Faster and less memory hungry thumbnail generation for TYPO3 with [libvips](https://libvips.github.io/libvips/)

Here is a speed and memory comparison: https://github.com/libvips/libvips/wiki/Speed-and-memory-use

## Prerequisites

The [PHP module vips](https://github.com/libvips/php-vips-ext) needs to be present on the server.

It can be installed with `pecl install vips`

## Installation

`composer require christophlehmann/vips`

## Configuration

By default the file types `jpg,jpeg,png,webp,pdf` are handled. Other types are handled through TYPO3.
If you have problems with certain types just deactivate them in EM.

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
