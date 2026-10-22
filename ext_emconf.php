<?php

$EM_CONF[$_EXTKEY] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [],
        'depends'   => [
            'foundation' => '4.1',
            'php'        => '8.3.0-8.5.99',
            'typo3'      => '13.4.0-14.99.99'
        ],
        'suggests'  => [],
    ],
    'description'      => 'Collection of useful ViewHelpers',
    'state'            => 'stable',
    'title'            => 'PSBits | ViewHelpers',
    'version'          => '3.1.1',
];
