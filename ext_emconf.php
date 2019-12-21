<?php

$EM_CONF['vips'] = [
    'title' => 'vips',
    'description' => 'Faster and less memory hungry thumbnail generation with vips php module',
    'category' => 'misc',
    'author' => 'Christoph Lehmann',
    'author_email' => 'post@christophlehmann.eu',
    'state' => 'beta',
    'clearCacheOnLoad' => 1,
    'version' => '0.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-10.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
