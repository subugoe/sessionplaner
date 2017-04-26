<?php
defined('TYPO3_MODE') || die('Access denied.');


call_user_func(function () {
    /**
     * Backend Module
     */
    if (TYPO3_MODE === 'BE') {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'Evoweb.sessionplaner',
            'web',
            'tx_sessionplaner_m1',
            '',
            [
                'Edit' => 'show',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:sessionplaner/Resources/Public/Icons/sessionplaner_module.png',
                'labels' => 'LLL:EXT:sessionplaner/Resources/Private/Language/locallang_mod.xml',
            ]
        );
    }
});
