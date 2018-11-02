<?php

########################################################################
# Extension Manager/Repository config file for ext "seo_basics".
########################################################################

$EM_CONF[$_EXTKEY] = [
    'title' => 'Basic SEO Features',
    'description' => 'Adds a separate field for the title-tag per page, easy and SEO-friendly keywords and description editing in a new module as well as a flexible Google Sitemap (XML).',
    'category' => 'fe',
    'version' => '0.12.0',
    'state' => 'stable',
    'modify_tables' => 'pages',
    'author' => 'Benni Mack',
    'author_email' => 'benni@typo3.org',
    'constraints' => [
        'depends' => [
            'typo3' => '>=9.5.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'realurl' => '0.0.0-0.0.0',
            'cooluri' => '0.0.0-0.0.0',
            'seo' => '>=9.5.0'
        ],
    ],
    'suggests' => [
    ],
];
