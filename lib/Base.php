<?php

require_once PARARIUSOFFICE_PLUGIN_PATH . '/lib/functions/load.php';

abstract class ParariusOffice_Base
{
	const VERSION = '1.2.1';
	
	private $_apis = array();

	public static function run()
	{
		$className = get_called_class();
		$instance = new $className;
	}

	private final function __construct()
	{
		$this->_setupRequirementsCheck();
		$this->_setupRoutes();
		$this->_setupLanguages();
		$this->_init();
	}

	public function getApi($specificApiKey = null)
	{
		$apiKeys = array_filter(array_map('trim', explode(',', get_option('nomis_api_key'))));
		$mainApiKey = $specificApiKey !== null ? $specificApiKey : $apiKeys[0];

		if (!isset($this->_api[$mainApiKey]))
		{
			try
			{
				require_once PARARIUSOFFICE_PLUGIN_PATH . '/lib/Api.php';

				$this->_api[$mainApiKey] = new Nomis_Api($mainApiKey);

				foreach ($apiKeys as $apiKey)
				{
					$this->_api[$mainApiKey]->addEndpoint(new Nomis_Api_Endpoint_Nomis($this->_api[$mainApiKey], $apiKey));
				}
			}
			catch (Nomis_Api_Exception $e)
			{
				wp_die($e->getMessage());
			}
		}
		
		try
		{
			$this->_api[$mainApiKey]->setLanguage(substr(get_bloginfo('language'), 0, 2));
		}
		catch (Nomis_Api_Exception $e)
		{
			wp_die($e->getMessage());
		}

		return $this->_api[$mainApiKey];
	}

	private function _setupRoutes()
	{
		add_action('init', function()
		{
			$rule = 'property/([^/]+)/([^/]+)/([^/]+)/?$';
			
			add_rewrite_rule($rule, 'index.php?pagename=property&propertyid=$matches[3]', 'top');
			
			$rules = get_option('rewrite_rules');

			if (!isset($rules[$rule]))
			{
				global $wp_rewrite;
				$wp_rewrite->flush_rules();
			}
		});
	}

	private function _setupLanguages()
	{
		add_action('init', function()
		{
			load_plugin_textdomain('parariusoffice', false, plugin_basename(PARARIUSOFFICE_PLUGIN_PATH) . '/l10n/');
		});
	}

	private function _setupRequirementsCheck()
	{
		register_activation_hook(PARARIUSOFFICE_INDEX_FILE, function()
		{
			if (version_compare(PHP_VERSION, '5.3.0', '>=') === false)
			{
				deactivate_plugins(PARARIUSOFFICE_INDEX_FILE);

				echo wp_sprintf(
					__('%1s: PHP-version not support (current: %s, needed: >= 5.3)'),
					__FILE__,
					PHP_VERSION
					);
			}
			
			if (function_exists('json_decode') === false)
			{
				deactivate_plugins(PARARIUSOFFICE_INDEX_FILE);

				_e('JSON-extension missing in PHP-installation', 'parariusoffice');
			}
		});
	}

	protected abstract function _init();
}
