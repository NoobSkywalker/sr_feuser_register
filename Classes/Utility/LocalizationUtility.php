<?php
namespace SJBR\SrFeuserRegister\Utility;

/*
 *  Copyright notice
 *
 *  (c) 2015-2018 Stanislas Rolland <typo3(arobas)sjbr.ca>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Translate a key from locallang. The files are loaded from the folder "Resources/Private/Language/".
 */
class LocalizationUtility
{
	/**
	 * @var array List of allowed suffixes
	 */
	static protected $allowedSuffixes = ['formal', 'informal'];

	/**
	 * @var string Configured suffix
	 */
	static protected $suffix = null;

	/**
	 * @var ConfigurationManagerInterface
	 */
	static protected $configurationManager = null;

	/**
	 * Returns the localized label of the LOCAL_LANG key, $key.
	 *
	 * @param string $key The key from the LOCAL_LANG array for which to return the value.
	 * @param string $extensionName The name of the extension
	 * @param array $arguments the arguments of the extension, being passed over to vsprintf
	 * @return string|NULL The value from LOCAL_LANG or NULL if no translation was found.
	 */
	static public function translate($key, $extensionName, $arguments = null)
	{
		$value = null;
		self::initializeLocalization($extensionName);
		// If the suffix is set and we have a localized string for the desired salutation, we'll take that.
		if (self::$suffix) {
			$expandedKey = $key . '_' . self::$suffix;
			$value = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($expandedKey, $extensionName, $arguments);
		}
		if ($value === null) {
			$value = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $extensionName, $arguments);
		}
		return $value;
	}

	/**
	 * Initializes the suffix
	 *
	 * @param string $extensionName
	 * @return void
	 */
	static protected function initializeLocalization($extensionName)
	{
		if (isset(self::$suffix)) {
			return;
		}
		$configurationManager = static::getConfigurationManager();
		$settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, $extensionName);
		if (isset($settings['salutation']) && in_array($settings['salutation'], self::$allowedSuffixes, true)) {
			self::$suffix = ($settings['salutation'] === 'formal' ? '' : $settings['salutation']);
		} else {
			self::$suffix = '';
		}
	}

	/**
	 * Returns instance of the configuration manager
	 *
	 * @return ConfigurationManager
	 */
	static protected function getConfigurationManager()
	{
		if (!is_null(static::$configurationManager)) {
			return static::$configurationManager;
		}
		$objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
		static::$configurationManager = $configurationManager;
		return $configurationManager;
	}
}