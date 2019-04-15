<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

//# Add page TSConfig
$pageTsConfig = '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:cleverreach/Configuration/TypoScript/TsConfig/Page/powermail.tsconfig">';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig($pageTsConfig);



