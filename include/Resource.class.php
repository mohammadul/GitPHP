<?php

require_once(GITPHP_BASEDIR . 'lib/php-gettext/streams.php');
require_once(GITPHP_BASEDIR . 'lib/php-gettext/gettext.php');

/**
 * Constant of the locale cookie in the user's browser (deprecated, use class const)
 */
defined('GITPHP_LOCALE_COOKIE') || define('GITPHP_LOCALE_COOKIE', 'GitPHPLocale');

/**
 * Locale cookie lifetime
 */
defined('GITPHP_LOCALE_COOKIE_LIFETIME') || define('GITPHP_LOCALE_COOKIE_LIFETIME', 60*60*24*365); // 1 year

/**
 * Resource factory class
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 */
class GitPHP_Resource
{
	/**
	 * Constant of the locale cookie in the user's browser
	 */
	const LocaleCookie = 'GitPHPLocale';

	/**
	 * Locale cookie lifetime
	 */
	const LocaleCookieLifetime = GITPHP_LOCALE_COOKIE_LIFETIME; // 1 year

	/**
	 * instance
	 *
	 * Stores the singleton instance of the resource provider
	 *
	 * @access protected
	 * @static
	 */
	protected static $instance = null;

	/**
	 * currentLocale
	 *
	 * Stores the currently instantiated locale identifier
	 *
	 * @access protected
	 * @static
	 */
	protected static $currentLocale = '';

	/**
	 * GetInstance
	 *
	 * Returns the singleton instance
	 *
	 * @access public
	 * @static
	 * @return mixed instance of resource class
	 */
	public static function GetInstance()
	{
		return self::$instance;
	}

	/**
	 * DestroyInstance
	 *
	 * Releases the singleton instance
	 *
	 * @access public
	 * @static
	 */
	public static function DestroyInstance()
	{
		self::$instance = null;
	}

	/**
	 * Instantiated
	 *
	 * Tests if the resource provider has been instantiated
	 *
	 * @access public
	 * @static
	 * @return boolean true if resource provider is instantiated
	 */
	public static function Instantiated()
	{
		return (self::$instance !== null);
	}

	/**
	 * GetLocale
	 *
	 * Gets the currently instantiated locale
	 *
	 * @access public
	 * @static
	 * @return string locale identifier
	 */
	public static function GetLocale()
	{
		return self::$currentLocale;
	}

	/**
	 * GetLocaleName
	 *
	 * Gets the current instantiated locale's name
	 *
	 * @access public
	 * @static
	 * @return string locale name
	 */
	public static function GetLocaleName()
	{
		return GitPHP_Resource::LocaleToName(self::$currentLocale);
	}

	/**
	 * LocaleToName
	 *
	 * Given a locale, returns a human readable name
	 *
	 * @access public
	 * @static
	 * @param string $locale locale
	 * return string name
	 */
	public static function LocaleToName($locale)
	{
		$localeName = __('English');		// for xgettext extraction

		$localeReader = null;
		if (self::$currentLocale == $locale) {
			$localeReader = self::$instance;
		}
		if (!$localeReader) {
			$localeReader = GitPHP_Resource::CreateLocale($locale, false);
		}
		if (!$localeReader) {
			return '';
		}

		$localeName = $localeReader->translate('English');

		if (!(($locale == 'en_US') || ($locale == 'en'))) {
			if ($localeName == 'English') {
				// someone didn't translate the language name - don't mislabel it as english
				return '';
			}
		}

		return $localeName;
	}

	/**
	 * SupportedLocales
	 *
	 * Gets the list of supported locales and their languages
	 *
	 * @access public
	 * @static
	 * @return array list of locales mapped to languages
	 */
	public static function SupportedLocales()
	{
		$locales = array();

		$locales['en_US'] = GitPHP_Resource::LocaleToName('en_US');

		if ($dh = opendir(GITPHP_LOCALEDIR)) {
			while (($file = readdir($dh)) !== false) {
				$fullPath = GITPHP_LOCALEDIR . '/' . $file;
				if ((strpos($file, '.') !== 0) && is_dir($fullPath) && is_file($fullPath . '/gitphp.mo')) {
					if ($file == 'zz_Debug') {
						$conf = GitPHP_Config::GetInstance();
						if ($conf) {
							if (!$conf->GetValue('debug', false)) {
								continue;
							}
						}
					}
					$locales[$file] = GitPHP_Resource::LocaleToName($file);
				}
			}
		}

		ksort($locales);
		
		return $locales;
	}

	/**
	 * FindPreferredLocale
	 *
	 * Given a list of preferred locales, try to find a matching supported locale
	 *
	 * @access public
	 * @static
	 * @param string $httpAcceptLang HTTP Accept-Language string
	 * @return string matching locale if found
	 */
	public static function FindPreferredLocale($httpAcceptLang)
	{
		if (empty($httpAcceptLang))
			return '';

		$locales = explode(',', $httpAcceptLang);

		$localePref = array();

		foreach ($locales as $locale) {
			$quality = '1.0';
			$localeData = explode(';', trim($locale));
			if (count($localeData) > 1) {
				$q = trim($localeData[1]);
				if (substr($q, 0, 2) == 'q=') {
					$quality = substr($q, 2);
				}
			}
			$localePref[$quality][] = trim($localeData[0]);
		}
		krsort($localePref);

		$supportedLocales = GitPHP_Resource::SupportedLocales();

		foreach ($localePref as $quality => $qualityArray) {
			foreach ($qualityArray as $browserLocale) {
				$locale = str_replace('-', '_', $browserLocale);
				$loclen = strlen($locale);

				foreach ($supportedLocales as $l => $lang) {
					/* 
					 * using strncasecmp with length of the preferred
					 * locale means we can match both full
					 * language + country preference specifications
					 * (en_US) as well as just language specifications
					 * (en)
					 */
					if (strncasecmp($locale, $l, $loclen) === 0) {
						return $l;
					}
				}
			}
		}
		return '';
	}

	/**
	 * Instantiate
	 *
	 * Instantiates the singleton instance
	 *
	 * @access public
	 * @static
	 * @param string $locale locale to instantiate
	 * @return boolean true if resource provider was instantiated successfully
	 */
	public static function Instantiate($locale)
	{
		self::$instance = null;
		self::$currentLocale = '';

		$localeReader = GitPHP_Resource::CreateLocale($locale);
		if (!$localeReader) {
			return false;
		}

		self::$instance = $localeReader;
		self::$currentLocale = $locale;
		return true;
	}

	/**
	 * CreateLocale
	 *
	 * Creates a locale reader object
	 *
	 * @access private
	 * @static
	 * @param string $locale locale to create
	 * @param boolean $cache false to disable caching
	 * @return mixed locale reader object or null on failure
	 */
	private static function CreateLocale($locale, $cache = true)
	{
		$reader = null;
		if (!(($locale == 'en_US') || ($locale == 'en'))) {
			$reader = new FileReader(GITPHP_LOCALEDIR . $locale . '/gitphp.mo');
			if (!$reader) {
				return null;
			}
		}

		return new gettext_reader($reader, $cache);
	}

}


/**
 * Gettext wrapper function for readability, single string
 *
 * @param string $str string to translate
 * @return string translated string
 */
function __($str)
{
	if (GitPHP_Resource::Instantiated())
		return GitPHP_Resource::GetInstance()->translate($str);
	return $str;
}

/**
 * Gettext wrapper function for readability, plural form
 *
 * @param string $singular singular form of string
 * @param string $plural plural form of string
 * @param int $count number of items
 * @return string translated string
 */
function __n($singular, $plural, $count)
{
	if (GitPHP_Resource::Instantiated())
		return GitPHP_Resource::GetInstance()->ngettext($singular, $plural, $count);
	if ($count > 1)
		return $plural;
	return $singular;
}

