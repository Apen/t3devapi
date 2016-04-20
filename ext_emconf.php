<?php

$EM_CONF[$_EXTKEY] = array(
    'title'            => 'TYPO3 Developer API',
    'description'      => 'A Powerful API for your (my ?) TYPO3 developments. No manual but the classes are well documented :-)',
    'category'         => 'misc',
    'version'          => '1.1.0',
    'state'            => 'stable',
    'uploadfolder'     => false,
    'createDirs'       => '',
    'clearcacheonload' => true,
    'author'           => 'CERDAN Yohann [Site-nGo]',
    'author_email'     => 'cerdanyohann@yahoo.fr',
    'author_company'   => 'Site\'nGo',
    'constraints'      =>
        array(
            'depends'   =>
                array(
                    'php'   => '5.3.7-7.0.99',
                    'typo3' => '6.2.0-7.6.99',
                ),
            'conflicts' =>
                array(),
            'suggests'  =>
                array(),
        ),
);

