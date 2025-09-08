<?php

$EM_CONF[$_EXTKEY] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [],
        'depends'   => [
            'foundation' => '3.0',
            'php'        => '8.3',
            'typo3'      => '12.4.0-13.4.99'
        ],
        'suggests'  => [],
    ],
    'description'      => 'Collection of useful ViewHelpers',
    'state'            => 'stable',
    'title'            => 'PSBits | ViewHelpers',
    'version'          => '3.1.0',
];
