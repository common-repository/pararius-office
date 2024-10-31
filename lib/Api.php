<?php

/**
 * Nomis properties app
 * 2.6
 * - Add a prospect to a property
 * 
 * 2.5.3
 * - Get all garages & search for 'em
 * 
 * 2.5.2
 * - Search for rent only
 * 
 * 2.5.1
 * - Get all locations
 * 
 * 2.5
 *  - Overloading made possible (private -> protected)
 * 
 * 2.4.2
 *  - Set the default cache path
 * 
 * 2.3.2
 *  - Negative country search (not in nl)
 *
 * 2.3.1
 *  - checkApiKey returns false on empty key
 *
 * 2.3.0
 *  - This version number fetches details for properties for sale
 *  - Search countries
 *
 * 2.2.2
 *  - Search inclusive on prices
 *
 * 2.2.1
 *  - Get default database files function for clear cache functionality
 *
 * 2.2.0
 *  - Support for completely customized request and declaration form
 *  - Get all available house types
 *
 * 2.1.1
 *  - default sort is price
 *
 * 2.1.0
 *  - Send the clients buildnumber to the server
 *  - Somewhat more advanced debug methods
 *  - Added extra template option (quicksearch)
 *
 * 2.004
 *  - Search on forsale prices
 *
 * 2.003
 *  - Sort by price
 *  - Emailaddress in contactform required
 *
 * 2.002
 *  - Support for extra fields in single request form
 *
 * 2.001
 *  - When property is deleted from backoffice, its still on the website [FIXED]
 *  - Major performance improvement while fetching the new data
 *
 * 2.000
 * - Incremental database updates (much, much, much, much, much, much & much faster -- it's even worth a 2.0 release :-)
 * - Date format regex
 * - Option to use the original house id @see 1.007, now enabled by default
 * - Sort random
 * - Related properties can be random (within criteria that is)
 *
 * 1.011
 *  - Order by date added, city, district, street, availability, bedrooms and forsale price
 *
 * 1.010
 *  - Search rent/sale
 *
 * 1.009
 *  - Search on surface
 *
 * 1.008
 *  - Option to get the current include dir
 *
 * 1.007
 *  - Option to use the original house id
 *
 * 1.006
 *  - Cache the API-key check for multiple calls
 *
 * 1.005
 *  - Get company email address
 *  - Order search results
 *  - Add request to single broker
 *
 * 1.004
 *  - Rental declaration form
 *  - All available districts are available in extraData
 *  - Search functionality for available_at, parking, garden, balcony, elevator, districts
 *  - Search changed to using continue; instead of break 2;
 *
 * 1.003
 *  - Added option to check if the API-key is valid
 *
 * 1.002:
 *  - Added CMS getters
 *  - Contact page
 *
 * 1.001:
 *  - Added support for custom directories
 *  - Added Nomis_View::shorten
 *  - Added some getters for additional information outside the API-class
 *
 * 1.000: Initial
 * 
 * 
 * 
 * 
 * INSTALL:
 *  - create cache dir
 *  - create template dir, with templates
 * 
 */

class Nomis_Api
{
	/**
	 * Build number
	 */
	const BUILD_NUMBER = '2.6';
	protected $_buildNumber = self::BUILD_NUMBER; /* Overloading made possible */
	
	/**
	 * Cache time to live
	 * 60 * 60 * 24	== 86400	a day
	 * 60 * 60		== 3600		an hour
	 * 60 * 15		== 900		quarter of an hour
	 * 60			== 60		gone in sixty seconds
	 * 6			== 6		six seconds
	 */
	const CACHE_TTL_DAY = 86400;
	const CACHE_TTL_HOUR = 3600;
	const CACHE_TTL_QUARTER = 900;
	
	const DEFAULT_CACHE_TTL = 900;
	
	const CACHE_TTL_MIN = 900;
	
	/**
	 * Default debug modus
	 * 1 -> enable error reporting
	 * 2 -> ignore cache
	 * 4 -> print response from server
	 * 8 -> after printing, kill the script
	 */
	const DEBUG_ERROR_REPORTING = 1;
	const DEBUG_IGNORE_CACHE = 2;
	const DEBUG_PRINT_RESPONSE = 4;
	const DEBUG_PRINT_RESPONSE_DIE = 8;
	const DEBUG_MODUS_ALL = 15;
	
	const DEFAULT_DEBUG_MODUS = 0; /* Debug purposes */
	protected static $_debugModus = self::DEFAULT_DEBUG_MODUS;
	
	/**
	 * Default language
	 */
	const DEFAULT_LANGUAGE = 'en';
	
	/**
	 * Default database files
	 */
	const DEFAULT_DATABASE_PATH = '';
	const DEFAULT_DATABASE_FILE_NL = 'cache/data-nl.db';
	const DEFAULT_DATABASE_FILE_EN = 'cache/data.db';
	const DEFAULT_DATABASE_PAGE_NL = 'cache/pages-nl.db';
	const DEFAULT_DATABASE_PAGE_EN = 'cache/pages.db';
	
	/**
	 *
	 */
	const DEFAULT_ENABLE_PAGES = false;
	
	/**
	 * Possible options
	 */
	const ACTION_GET_PROPERTIES = 'getproperties';
	const ACTION_ADD_REQUEST = 'add-request';
	const ACTION_GET_PAGES = 'get-pages';
	const ACTION_CONTACT = 'contact';
	const ACTION_CHECK_API_KEY = 'check-api-key';
	const ACTION_ADD_RENTAL_DECLARATION = 'add-rental-declaration';
	const ACTION_GET_COMPANY_EMAILADDRESS = 'get-company-emailaddress';
	const ACTION_ADD_SINGLE_REQUEST = 'add-single-request';
	const ACTION_ADD_PROSPECT = 'add-prospect';
	
	/**
	 * Default properties per page
	 */
	const PROPERTIES_PER_PAGE = 10;
	const DEFAULT_RANDOM_RELATED_PROPERTIES = true;
	
	/**
	 * API-server details
	 */
	const API_HOST = 'http://api.parariusoffice.nl/db.php';
	const API_PORT = 80;
	const API_VERSION = 2;
	protected $_apiVersion = self::API_VERSION; /* Overloading made possible */
	
	/**
	 * Date format for the request pages
	 */
//	const DATE_FORMAT_REGEX = '/^\d{1,2}-\d{1,2}-\d{4}$/';
	const DATE_FORMAT_REGEX = '/^(0?[1-9]|[12][0-9]|3[01])-(0?[1-9]|1[012])-(19|20)\d\d$/';
	const DATE_FORMAT_HUMAN_READABLE = 'dd-mm-yyyy';
	
	const REQUEST_COOKIE_NAME = 'nomis_request_values';
	
	private $_databasePath;
	private $_databaseFile;
	private $_databaseFileTouched = false;
	protected $_apiKey;
	protected $_language;
	protected $_cacheTtl;
	private $_render;
	protected $_propertiesPerPage;
	protected $_randomRelatedProperties;
	private $_includeDir;
	
	private static $_customRequestForm;
	private static $_customRentalDeclarationForm;
	
	/**
	 * Is the pages module loaded
	 * @var boolean
	 */
	private $_enablePages;
	
	/**
	 * Path to the database file for the pages
	 * @var string
	 */
	private $_databasePage;
	
	/**
	 * Is the page database file 'touched'
	 * @var boolean
	 */
	private $_databasePageTouched = false;
	
	/**
	 * Additional outside information
	 */
	protected $_searchResults;
	protected $_propertyInformation;
	protected $_pageInformation;
	
	public $templateFiles = array(
		'search' => 'templates/search.phtml',
		'property' => 'templates/property.phtml',
		'request' => 'templates/request.phtml', // lead
		'_field' => 'templates/_field.phtml',
		'page' => 'templates/page.phtml',
		'contact' => 'templates/contact.phtml',
		'rental-declaration' => 'templates/rental-declaration.phtml',
		'add-single-request' => 'templates/single-request.phtml', // request directly to broker
		'quicksearch' => 'templates/quicksearch.phtml',
		'add-prospect' => 'templates/prospect.phtml'
	);
	
	/**
	 * HTTP-response codes
	 */
	protected static $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
		
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
		
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
		
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
		
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
	
	private $_endpoints = array();

	/**
	 * Create an API-object for communicating with the API
	 * @param string $apiKey The API-key to identify yourself to the API-server
	 * @param boolean load the pages module
	 */
	public function __construct($apiKey, $databasePath = self::DEFAULT_DATABASE_PATH, $debugModus = null)//, $databaseFile = self::DEFAULT_DATABASE_FILE_EN) $enablePages = self::DEFAULT_ENABLE_PAGES, 
	{
		if ($debugModus !== null)
		{
			self::debug($debugModus);
		}

		if (self::debug() & self::DEBUG_ERROR_REPORTING)
		{
			error_reporting(E_ALL);
			@ini_set('display_errors', '1');
			
			// some xdebug options to show more info
			@ini_set('xdebug.var_display_max_depth', 8);
			@ini_set('xdebug.trace_format', 0);
			@ini_set('xdebug.collect_params', 2);
			@ini_set('xdebug.collect_return', 1);
			@ini_set('xdebug.var_display_max_data', 2000);
			@ini_set('xdebug.var_display_max_children', 2000);
		}

		$this->_apiKey = $apiKey;
		$this->_enablePages = (bool) false;//$enablePages;

		// $this->setDatabaseFile($databaseFile);

		// some defaults
		
		
		if (!empty($databasePath) && is_dir($databasePath))
		{
			$databasePath = $databasePath;
		}
		else
		{
			$databasePath = dirname(__FILE__) . '/../';
		}
		
		$this->_databasePath = $databasePath;
		$this->setLanguage();
		$this->setCacheTtl();
		$this->setRenderMode();
		$this->setPropertiesPerPage();
		$this->setRandomRelatedProperties();
		$this->setIncludeDir();
	}
	
	public function setEndpoint(Nomis_Api_Endpoint $endpoint)
	{
		$this->_endpoints = array($endpoint);
	}

	public function addEndpoint(Nomis_Api_Endpoint $endpoint)
	{
		$this->_endpoints[] = $endpoint;
	}

	public function getCacheTtl()
	{
		return $this->_cacheTtl;
	}

	public function getDefaultDatabaseFiles()
	{
		return array(
			'data-nl' => realpath(dirname(__FILE__) . '/../' . self::DEFAULT_DATABASE_FILE_NL),
			'data-en' => realpath(dirname(__FILE__) . '/../' . self::DEFAULT_DATABASE_FILE_EN),
			'page-nl' => realpath(dirname(__FILE__) . '/../' . self::DEFAULT_DATABASE_PAGE_NL),
			'page-en' => realpath(dirname(__FILE__) . '/../' . self::DEFAULT_DATABASE_PAGE_EN)
		);
	}
	
	public static function debug($level = null)
	{
		if ($level !== null)
		{
			self::$_debugModus = $level;
		}

		return self::$_debugModus;
	}
	
	public function checkApiKey()
	{
		if (empty($this->_apiKey))
		{
			return false;
		}

		static $_checkApiKey = null;

		if ($_checkApiKey === null)
		{
			$rs = $this->_fetchData(self::ACTION_CHECK_API_KEY);

			$_checkApiKey = !empty($rs['result']) && $rs['result'] == '1';
		}

		return $_checkApiKey;
	}
	
	/**
	 * Set the language to be used
	 * @param string $language
	 * @return Nomis_Api fluent interface
	 */
	public function setLanguage($language = self::DEFAULT_LANGUAGE)
	{
		$this->_language = $language;

		switch ($this->_language)
		{
			case 'nl':
				$this->setDatabaseFile(self::DEFAULT_DATABASE_FILE_NL);

				if ($this->_enablePages)
				{
					$this->setPageDatabase(self::DEFAULT_DATABASE_PAGE_NL);
				}
				break;

			case 'en':
				$this->setDatabaseFile(self::DEFAULT_DATABASE_FILE_EN);

				if ($this->_enablePages)
				{
					$this->setPageDatabase(self::DEFAULT_DATABASE_PAGE_EN);
				}
				break;
		}

		return $this;
	}
	
	public function setDatabaseFile($databaseFile = self::DEFAULT_DATABASE_FILE_EN)
	{
		$this->_databaseFile = realpath($this->_databasePath . $databaseFile);
		
		if ($this->_databaseFile === false || !file_exists($this->_databaseFile))
		{
			if (touch($this->_databaseFile))
			{
				$this->_databaseFileTouched = true;
				$this->_databaseFile = realpath($databaseFile);
			}
			else
			{
				throw new Nomis_Api_Exception('Invalid database file');
			}
		}

		if (!is_writeable($this->_databaseFile))
		{
			throw new Nomis_Api_Exception('Database is not writable');
		}

		return $this;
	}
	
	/**
	 * Set the database file for the pages
	 * @param type $databaseFile
	 * @since 1.002
	 * @return Nomis_Api provide a fluent interface
	 */
	public function setPageDatabase($databaseFile = self::DEFAULT_DATABASE_PAGE_EN)
	{
		if (!$this->_enablePages)
		{
			throw new Nomis_Api_Exception('Pages not enabled');
		}

		$this->_databasePage = realpath(dirname(__FILE__) . '/../' . $databaseFile);

		if ($this->_databasePage === false)
		{
			if (touch($databaseFile))
			{
				$this->_databasePageTouched = true;
				$this->_databasePage = realpath($databaseFile);
			}
			else
			{
				throw new Nomis_Api_Exception('Invalid page database file');
			}
		}

		if (!is_writeable($this->_databasePage))
		{
			throw new Nomis_Api_Exception('Page database is not writable');
		}

		return $this;
	}
	
	public function setCacheTtl($seconds = self::DEFAULT_CACHE_TTL)
	{
		if ($seconds < self::CACHE_TTL_MIN && $seconds != self::DEFAULT_CACHE_TTL)
		{
			throw new Nomis_Api_Exception('Invalid cache TTL');
		}

		$this->_cacheTtl = $seconds;

		return $this;
	}
	
	public function setRenderMode($render = true)
	{
		$this->_render = (bool) $render;

		return $this;
	}
	
	public function setPropertiesPerPage($ppp = self::PROPERTIES_PER_PAGE)
	{
		$this->_propertiesPerPage = (int) $ppp;

		return $this;
	}
	
	public function getPropertiesPerPage()
	{
		return $this->_propertiesPerPage;
	}
	
	public function setRandomRelatedProperties($random = self::DEFAULT_RANDOM_RELATED_PROPERTIES)
	{
		$this->_randomRelatedProperties = (bool) $random;

		return $this;
	}
	
	public function setCustomRequestForm(array $form)
	{
		self::$_customRequestForm = $form;

		return $this;
	}
	
	public function setCustomRentalDeclarationForm(array $form)
	{
		self::$_customRentalDeclarationForm = $form;

		return $this;
	}
	
	/**
	 * Set the directory where to find the templates
	 * @since 1.001
	 * @param string new include directory
	 * @return old include dir
	 */
	public function setIncludeDir($dir = null)
	{
		$oldIncludeDir = $this->_includeDir;

		if ($dir === null)
		{
			$dir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
		}
		else
		{
			$dir = realpath($dir);

			if ($dir === false)
			{
				throw new Nomis_Api_Exception('Invalid directory');
			}
		}

		$this->_includeDir = $dir . DIRECTORY_SEPARATOR;

		return $oldIncludeDir;
	}

	public function getIncludeDir()
	{
		return $this->_includeDir;
	}

	public function getPropertyInformation()
	{
		return $this->_propertyInformation;
	}

	public function getSearchResults()
	{
		return $this->_searchResults;
	}

	public function getPageInformation()
	{
		return $this->_pageInformation;
	}

	public function run($input)
	{
		$contents = '';
		if (isset($input['action']))
		{
			switch ($input['action'])
			{
				case 'search':
					$contents = $this->_render(
						$this->templateFiles['search'],
						$this->_search($input)
					);
					break;

				case 'property':
					$contents = $this->_render(
						$this->templateFiles['property'],
						$this->_getProperty($input)
					);
					break;

				case 'property-request':
					$contents = $this->_render(
						$this->templateFiles['request'],
						$this->_processRequest($input)
					);
					break;

				case 'request':
					$contents = $this->_render(
						$this->templateFiles['request'],
						$this->_processRequest($input)
					);
					break;

				case 'rental-declaration':
					$contents = $this->_render(
						$this->templateFiles['rental-declaration'],
						$this->_processRentalDeclaration($input)
					);
					break;

				case 'all-cities':
					$contents = $this->_getAllCities();
					break;

				case 'page':
					if (!$this->_enablePages)
					{
						throw new Nomis_Api_Exception('Pages not enabled');
					}

					$contents = $this->_render(
						$this->templateFiles['page'],
						$this->_page($input)
					);
					break;

				case 'add-single-request':
					$contents = $this->_render(
						$this->templateFiles['add-single-request'],
						$this->_addSingleRequest($input)
					);
					break;

				case 'contact':
					$contents = $this->_render(
						$this->templateFiles['contact'],
						$this->_contact($input)
					);
					break;

				case 'quicksearch':
					$contents = $this->_render(
						$this->templateFiles['quicksearch'],
						$this->_search($input)
					);
					break;
				
				case 'add-prospect':
					$contents = $this->_render(
						$this->templateFiles['add-prospect'],
						$this->_addProspect($input)
					);
					break;

				default:
					throw new Nomis_Api_Exception('Invalid page');
					break;
			}
		}
		else
		{
			$contents = $this->_render(
				$this->templateFiles['search']
			);
		}

		return $contents;
	}

	private function _render($template, $data = null)
	{
		if ($this->_render)
		{
			$view = new Nomis_View(
				$this->_includeDir,
				$template,
				$data,
				array(
					'cities' => $this->_getAllCities(),
					'districts' => $this->_getAllDistricts(),
					'house_types' => $this->_getAllHouseTypes(),
					'countries' => $this->_getAllCountries()
				)
			);

			ob_start();
			$view->display();
			$contents = ob_get_contents();
			ob_end_clean();
		}
		else
		{
			$contents = $data;
		}

		return $contents;
	}
	
	protected function _getAllFieldValues($field)
	{
		static $_values = array();
		
		if (isset($_values[$field]))
		{
			return $_values[$field];
		}
		
		$_values[$field] = array();
		$tmp = array();

		$houses = $this->_getAllProperties();

		foreach ($houses['result'] as $house)
		{
			if (!empty($house[$field]) && !in_array(strtolower($house[$field]), $tmp))
			{
				$tmp[] = strtolower($house[$field]);
				$_values[$field][] = $house[$field];
			}
		}

		natcasesort($_values[$field]);

		return $_values[$field];
	}

	protected function _getAllCities()
	{
		return $this->_getAllFieldValues('city');
	}

	public function getAllCities()
	{
		return $this->_getAllCities();
	}

	protected function _getAllDistricts()
	{
		$districts = array();
		$tmp = array();

		$houses = $this->_getAllProperties();

		foreach ($houses['result'] as $house)
		{
			if (!empty($house['district']))
			{
				if (!isset($districts[strtolower($house['city'])]))
				{
					$districts[strtolower($house['city'])] = array();
					$tmp[strtolower($house['city'])] = array();
				}

				if (!in_array(strtolower($house['district']), $tmp[strtolower($house['city'])]))
				{
					$tmp[strtolower($house['city'])][] = strtolower($house['district']);
					$districts[strtolower($house['city'])][] = $house['district'];
				}
			}
		}

		foreach ($districts as $c => &$d)
		{
			natcasesort($d);
		}

		ksort($districts);

		return $districts;
	}
	
	public function getAllDistricts()
	{
		return $this->_getAllDistricts();
	}
	
	protected function _getAllCountries()
	{
		return $this->_getAllFieldValues('country');
	}

	protected function _getAllHouseTypes()
	{
		return $this->_getAllFieldValues('house_type');
	}

	public function getAllHouseTypes()
	{
		return $this->_getAllHouseTypes();
	}
	
	protected function _getAllInteriors()
	{
		return $this->_getAllFieldValues('interior');
	}
	
	public function getAllInteriors()
	{
		return $this->_getAllInteriors();
	}
	
	public function getAllLocations()
	{
		return $this->_getAllLocations();
	}
	
	protected function _getAllLocations()
	{
		return $this->_getAllFieldValues('location');
	}
	
	public function getAllParkings()
	{
		return $this->_getAllParkings();
	}
	
	protected function _getAllParkings()
	{
		return $this->_getAllFieldValues('parking');
	}

	protected static $_cityAliases = array(
		'den haag' => '\'s-gravenhage',
		'\'s-gravenhage' => 'den haag',
		'den bosch' => '\'s-hertogenbosch',
		'\'s-hertogenbosch' => 'den bosch',
	);

	protected function _search($values)
	{
		$houses = $this->_getAllProperties();
		$houses = $houses['result'];

		$fields = array(
			'min-price',
			'max-price'
		);

		$search = array();

		$cities = array();
		if (!empty($values['city']))
		{
			$tmpCities = array_map('strtolower', array_map('trim', explode(',', $values['city'])));

			foreach ($tmpCities as $city)
			{
				$cities[] = $city;

				if (isset(self::$_cityAliases[$city]))
				{
					$cities[] = self::$_cityAliases[$city];
				}
			}
		}

		$districts = array();
		if (!empty($values['district']))
		{
			$districts = array_map('strtolower', array_map('trim', explode(',', $values['district'])));
		}

		$countries = array();
		if (!empty($values['country']))
		{
			$countries = array_map('strtolower', array_map('trim', explode(',', $values['country'])));
		}

		$notCountries = array();
		if (!empty($values['not-country']))
		{
			$notCountries = array_map('strtolower', array_map('trim', explode(',', $values['not-country'])));
		}

		$houseTypes = array();
		if (!empty($values['house-types']))
		{
			$houseTypes = array_map('strtolower', array_map('trim', explode(',', $values['house-types'])));
		}

		$notHouseTypes = array();
		if (!empty($values['not-house-types']))
		{
			$notHouseTypes = array_map('strtolower', array_map('trim', explode(',', $values['not-house-types'])));
		}
		
		$forrentFrontStatusses = array();
		if (!empty($values['forrent_front_status']))
		{
			$forrentFrontStatusses = array_map('trim', explode(',', $values['forrent_front_status']));
		}
		
		$notForrentFrontStatusses = array();
		if (!empty($values['not-forrent_front_status']))
		{
			$notForrentFrontStatusses = array_map('trim', explode(',', $values['not-forrent_front_status']));
		}
		
		$divisions = array();
		if (!empty($values['divisions']))
		{
			$divisions = array_map('strtolower', array_map('trim', explode(',', $values['divisions'])));
		}
		
		$range = null;
		$geo = false;
		if (!empty($values['geo']) && !empty($values['enable-geo']))
		{
			list($lat, $lng) = array_map('trim', explode(',', $values['geo']));
			$lat = (float) $lat;
			$lng = (float) $lng;
			
			$range = !empty($values['range']) ? (float) $values['range'] : 0.03;
			
			$left = $lat - $range;
			$right = $lat + $range;
			
			$top = $lng - $range;
			$bottom = $lng + $range;
			
			$geo = true;
		}

		$rs = array();
		foreach ($houses as $id => &$house)
		{
			foreach ($values as $index => $value)
			{
				switch ($index)
				{
					case 'enable-geo':
						if ($geo && (empty($house['lat']) || empty($house['lng'])
						 || (float) $house['lat'] < $left || (float) $house['lat'] > $right
						 || (float) $house['lng'] < $top || (float) $house['lng'] > $bottom))
						{
							unset($houses[$id]);
							continue;
						}
						
						break;
					
					case 'for-sale':
						if ($value === '1' || $value === '0') $search['for-sale'] = $value;

						// when not for sale, unset
						if ($value === '1' && $house['forsale'] != '1')
						{
							unset($houses[$id]);
							continue;
						}

						// when not for rent, unset
						if ($value === '0' && $house['forrent'] != '1')
						{
							unset($houses[$id]);
							continue;
						}
						break;
					
					case 'for-rent':
						if (!empty($value)) $search['for-rent'] = $value;
						if (ctype_digit((string) $value) && $house['forrent'] != (string) $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;
					
					case 'min-price':
						if (!empty($value)) $search['min-price'] = $value;
						if (ctype_digit((string) $value) && $house['price'] < $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'max-price':
						if (!empty($value)) $search['max-price'] = $value;
						if (ctype_digit((string) $value) && $house['price'] > $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'forsale-min-price':
						if (!empty($value)) $search['forsale-min-price'] = $value;
						if (ctype_digit((string) $value) && $house['forsale_price'] < $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'forsale-max-price':
						if (!empty($value)) $search['forsale-max-price'] = $value;
						if (ctype_digit((string) $value) && $house['forsale_price'] > $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'city':
						if (!empty($value)) $search['city'] = $value;
						if (!empty($value) && !in_array(strtolower($house['city']), $cities))// != strtolower($value))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'country':
						if (!empty($value)) $search['country'] = $value;
						if (!empty($value) && !in_array(strtolower($house['country']), $countries))// != strtolower($value))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'not-country':
						if (!empty($value)) $search['not-country'] = $value;
						if (!empty($value) && in_array(strtolower($house['country']), $notCountries))// != strtolower($value))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'district':
						if (!empty($value)) $search['district'] = $value;
						if (!empty($value) && !in_array(strtolower($house['district']), $districts))// != strtolower($value))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'house_type':
						if (!empty($value)) $search['house_type'] = $value;
						if (!empty($value) && stripos($house['house_type'], $value) === false
						 || !empty($value) && empty($house['house_type']))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'house-types':
						if (!empty($value)) $search['house-types'] = $value;
						if (!empty($value) && !in_array(strtolower($house['house_type']), $houseTypes))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'not-house-types':
						if (!empty($value)) $search['not-house-types'] = $value;
						if (!empty($value) && in_array(strtolower($house['house_type']), $notHouseTypes))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'interior':
						if (!empty($value)) $search['interior'] = $value;
						if (!empty($value) && (stripos($house['interior'], $value) === false
						 || strtolower($house['interior']) == 'unfurnished' && strtolower($value) == 'furnished'))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'bedrooms':
						if (!empty($value)) $search['bedrooms'] = $value;
						if (ctype_digit((string) $value) && $house['bedrooms'] < $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'available_at':
						if (!empty($value)) $search['available_at'] = $value;
						if (!empty($value) && strtotime($value) < strtotime($house['available_at']))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'parking':
						if (!empty($value)) $search['parking'] = $value;
						if (!empty($value) && stripos($house['parking'], $value) !== false
						 || !empty($value) && empty($house['parking']))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'garden':
						if (!empty($value)) $search['garden'] = $value;
						if (!empty($value) && stripos($house['garden'], $value) !== false
						 || !empty($value) && empty($house['garden']))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'balcony':
						if (!empty($value)) $search['balcony'] = $value;
						if (!empty($value) && stripos($house['balcony'], $value) !== false
						 || !empty($value) && empty($house['balcony']))
						{
							unset($houses[$id]);
							continue;
						}
						break;
					case 'elevator':
						if (!empty($value)) $search['elevator'] = $value;
						if (!empty($value) && $house['elevator'] != $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;

					case 'surface':
						if (!empty($value)) $search['surface'] = $value;
						if (ctype_digit((string) $value) && $house['surface'] < $value)
						{
							unset($houses[$id]);
							continue;
						}
						break;

					case 'forrent_front_status':
						if (!empty($value)) $search['forrent_front_status'] = $value;
						if (!empty($value) && !in_array($house['forrent_front_status'], $forrentFrontStatusses))// != strtolower($value))
						{
							unset($houses[$id]);
							continue;
						}
						break;

					case 'divisions':
						if (!empty($value)) $search['divisions'] = $value;
						if (!empty($value) && !in_array($house['division'], $divisions))// != strtolower($value))
						{
							unset($houses[$id]);
							continue;
						}
						break;
				}
			}
		}

		if (!empty($values['order']) && preg_match('~^(price|online_date|date|city|street|district|bedrooms|forsale_price|available|random)((,|=)(asc|desc))?$~i', $values['order'], $match))
		{
			switch ($match[1])
			{
				case 'price':
					uasort($houses, array('self', '_priceSort'));
					break;

				case 'online_date':
					uasort($houses, array('self', '_onlineDateSort'));
					break;

				case 'date':
					uasort($houses, array('self', '_dateSort'));
					break;

				case 'city':
					uasort($houses, array('self', '_citySort'));
					break;

				case 'district':
					uasort($houses, array('self', '_districtSort'));
					break;

				case 'street':
					uasort($houses, array('self', '_streetSort'));
					break;

				case 'bedrooms':
					uasort($houses, array('self', '_bedroomsSort'));
					break;

				case 'available':
					uasort($houses, array('self', '_availableSort'));
					break;

				case 'forsale_price':
					uasort($houses, array('self', '_forsalePriceSort'));
					break;

				case 'random':
					shuffle($houses);
					break;
			}

			$search['order'] = $match[1] . (!empty($match[4]) ? '=' . $match[4] : '');

			if (isset($match[4]) && $match[4] == 'desc')
			{
				$houses = array_reverse($houses, true);
			}
		}
		else
		{
			uasort($houses, array('self', '_priceSort'));
		}

		$totalHouses = count($houses);

		$data = array_chunk($houses, $this->_propertiesPerPage, true);

		$page = 1;
		if (!empty($values['page']) && ctype_digit((string) $values['page']) && $values['page'] > 0 && $values['page'] <= count($data))
		{
			$search['page'] = $page = $values['page'];
		}

		return $this->_searchResults = array(
			'search' => $search,
			'page' => $page,
			'total_pages' => count($data),
			'total' => $totalHouses,
			'result' => empty($data[$page - 1]) ? array() : $data[$page - 1]
		);

		return $houses;
	}

	private static function _priceSort($one, $two)
	{
		if ($one['price'] < $two['price']) return -1;
		if ($one['price'] == $two['price']) return 0;
		if ($one['price'] > $two['price']) return 1;
	}

	private static function _onlineDateSort($one, $two)
	{
		$oneTime = strtotime($one['online_date']);
		$twoTime = strtotime($two['online_date']);

		if ($oneTime < $twoTime) return -1;
		if ($oneTime == $twoTime) return 0;
		if ($oneTime > $twoTime) return 1;
	}

	private static function _dateSort($one, $two)
	{
		$oneTime = strtotime($one['registration_date']);
		$twoTime = strtotime($two['registration_date']);

		if ($oneTime < $twoTime) return -1;
		if ($oneTime == $twoTime) return 0;
		if ($oneTime > $twoTime) return 1;
	}

	private static function _citySort($one, $two)
	{
		return strcasecmp($one['city'], $two['city']);
	}

	private static function _streetSort($one, $two)
	{
		return strcasecmp($one['street'], $two['street']);
	}

	private static function _districtSort($one, $two)
	{
		return strcasecmp($one['district'], $two['district']);
	}

	private static function _bedroomsSort($one, $two)
	{
		if ($one['bedrooms'] < $two['bedrooms']) return -1;
		if ($one['bedrooms'] == $two['bedrooms']) return 0;
		if ($one['bedrooms'] > $two['bedrooms']) return 1;
	}

	private static function _availableSort($one, $two)
	{
		$oneTime = strtotime($one['available_at_start']);
		$twoTime = strtotime($two['available_at_start']);

		if ($oneTime < $twoTime) return -1;
		if ($oneTime == $twoTime) return 0;
		if ($oneTime > $twoTime) return 1;
	}

	private static function _forsalePriceSort($one, $two)
	{
		if ($one['forsale_price'] < $two['forsale_price']) return -1;
		if ($one['forsale_price'] == $two['forsale_price']) return 0;
		if ($one['forsale_price'] > $two['forsale_price']) return 1;
	}

	protected function _getProperty($input)
	{
		$properties = $this->_getAllProperties();

		if ($this->_randomRelatedProperties)
		{
			$search = $this->_search(array_merge($input, array(
				'order' => 'random'
			)));
		}
		else
		{
			$search = $this->_search($input);
		}

		$property = null;

		if (isset($input['id']))
		{
			foreach ($properties['result'] as $propertyId => $propertyInfo)
			{
				if (strcasecmp($propertyId, $input['id']) === 0)
				{
					$property = $propertyInfo;
					$property['id'] = $propertyId;

					break;
				}
			}
		}

		$this->_propertyInformation = $property;
		return array(
			'search' => $search,
			'property' => $property
		);
	}

	private function _page($input)
	{
		$pages = $this->_getAllPages();
		$page = null;

		if (isset($input['id']) && !empty($pages['result'][$input['id']]))
		{
			$page = $pages['result'][$input['id']];
		}

		$this->_pageInformation = $page;
		return $page;
	}

	private function _contact($input)
	{
		$contact = array(
			'form' => array(
				'last_name' => array(
					'options' => array(
						'required' => true,
						'label' => 'Name'
					)
				),
				'emailaddress' => array(
					'options' => array(
						'label' => 'Email address',
						'regex' => 'email',
						'required' => true
					)
				),
				'telephone' => array(),
				'subject' => array(),
				'message' => array()
			)
		);

		$contact['error'] = false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_contact']))
		{
			if ($this->_validateForm($contact))
			{
				$tmp = $_POST;
				$tmp['name'] = $tmp['last_name'];

				$rs = $this->_fetchData(self::ACTION_CONTACT, $tmp);

				// var_dump($rs);

				if ($rs['result'] == '1')
				{
					$contact['success'] = true;
				}
			}
		}

		return array(
			'contact' => $contact
		);
	}

	/**
	 *
	 * @param type $input
	 * @return type
	 */
	private function _processRequest($input)
	{
		$otherIntel = $this->_getProperty($input);

		$request = array(
			'form' => array(
//				'title' => array(
//					'options' => array(
//						'required' => true
//					)
//				),
				'last_name' => array(
					'options' => array(
						'required' => true
					)
				),
				'last_name_prefix' => array(
					'options' => array(
						'type' => 'hidden'
					)
				),
				'first_name' => array(),
				'company' => array(),
				'telephone' => array(
					'options' => array(
						'required' => true,
					)
				),
				'emailaddress' => array(
					'options' => array(
						'required' => true,
						'label' => 'Emailaddress',
						'regex' => 'email'
					)
				)
			)
		);

		if ($otherIntel['property'] === null)
		{
			$request['form'] += array(
				'max_price' => array(
					'options' => array(
						'label' => 'Budget',
						'required' => true,
						'regex' => 'number',
						'type' => 'select',
						'options' => array(
							'' => '---',
							500 => 500,
							1000 => 1000,
							1500 => 1500,
							2000 => 2000,
							2500 => 2500,
							3000 => 3000,
							3500 => 3500,
							4000 => 4000
						)
					)
				),
				'interior' => array(
					'options' => array(
						'required' => true,
						'type' => 'select',
						'options' => array(
							'' => '',
							13 => 'Unfurnished',
							14 => 'Furnished',
							15 => 'Bare'
						)
					)
				),
				'bedrooms' => array(
					'options' => array(
						'required' => true,
						'type' => 'select',
						'options' => array(
							'' => '',
							'1' => '1+',
							'2' => '2+',
							'3' => '3+',
							'4' => '4+'
						)
					)
				),
				'city' => array(
					'options' => array(
						'required' => true
					)
				)
			);
		}

		$request['form'] += array(
			'commencing_date' => array(
				'options' => array(
					'description' => 'Commencing date in ' . self::DATE_FORMAT_HUMAN_READABLE,
					'label' => 'When do you want to rent',
					'class' => 'date',
					'regex' => 'date'
				)
			),
			'other_wishes' => array(
				'options' => array(
					'label' => 'Other wishes'
				)
			)
		);

		$request['error'] = false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_request']))
		{
			if (!headers_sent())
			{
				setcookie(self::REQUEST_COOKIE_NAME, base64_encode(serialize($_POST)), 0, '/', '.' . $_SERVER['HTTP_HOST'], false, true);
			}

			if ($this->_validateForm($request))
			{
				$ids = array();
				if ($otherIntel['property'] !== null)
				{
					foreach ($otherIntel['property']['broker'] as $broker)
					{
						$ids[] = $broker['house_id'];
					}
				}

				$rs = $this->_fetchData(self::ACTION_ADD_REQUEST, array_merge($_POST, array(
					'house_id' => $ids,
				)));

				// var_dump($rs);

				if ($rs['result'] == '1')
				{
					$request['success'] = true;
				}
			}
		}
		else
		{
			if (isset($_COOKIE[self::REQUEST_COOKIE_NAME]))
			{
				$tmp = base64_decode($_COOKIE[self::REQUEST_COOKIE_NAME]);

				if ($tmp !== false)
				{
					$level = error_reporting(0);
					$tmp = unserialize($tmp);
					error_reporting($level);

					if ($tmp !== false)
					{
						$newForm = array();
						foreach ($request['form'] as $name => $info)
						{
							$info['info'] = array();
							if (!empty($tmp[$name]))
							{
								$info['info'] = array(
									'value' => $tmp[$name]
								);
							}
							$newForm[$name] = $info;
						}

						$request['form'] = $newForm;
					}
				}
			}
		}

		return array_merge($otherIntel, array(
			'request' => $request
		));
	}

	private function _addSingleRequest($input)
	{
		$request = array(
			'form' => array(
				'last_name' => array(
					'options' => array(
						'required' => true,
						'label' => 'Name'
					)
				),
				'company' => array(),
				'telephone' => array(),
				'emailaddress' => array(
					'options' => array(
						'regex' => 'email'
					)
				),
				'interior' => array(
					'options' => array(
						'type' => 'select',
						'options' => array(
							'' => '',
							13 => 'Unfurnished',
							14 => 'Furnished',
							15 => 'Bare'
						)
					)
				),
				'bedrooms' => array(
					'options' => array(
						'type' => 'select',
						'options' => array(
							'' => '',
							'1' => '1+',
							'2' => '2+',
							'3' => '3+',
							'4' => '4+'
						)
					)
				),
				'city' => array(
					// 'required' => true
				),
				'district' => array(),
				'commencing_date' => array(
					'options' => array(
						'description' => 'Commencing date in ' . self::DATE_FORMAT_HUMAN_READABLE,
						'label' => 'When do you want to rent',
						'class' => 'regex',
						'regex' => 'date'
					)
				),
				'max_price' => array(
					'options' => array(
						// 'required' => true,
						'regex' => 'number'
					)
				),
				'other_wishes' => array()
			)
		);

		if (!empty(self::$_customRequestForm))
		{
			$request['form'] = self::$_customRequestForm;
		}

		$request['error'] = false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_single_request']))
		{
			if ($this->_validateForm($request))
			{
				unset($_POST['add_single_request']);

				$tmp = $_POST;
				$tmp['name'] = $tmp['last_name'];
			
				$rs = $this->_fetchData(self::ACTION_ADD_SINGLE_REQUEST, $tmp);

				if (!empty($rs['result']))
				{
					$request['success'] = true;
				}
			}
		}

		return $request;
	}

	private function _processRentalDeclaration($input)
	{
		$addDeclaration = array(
			'form' => array(
				'last_name' => array(
					'options' => array(
						'required' => true,
						'label' => 'Name'
					)
				),
				'address' => array(
					'options' => array(
						'required' => true
					)
				),
				'zipcode' => array(
					'options' => array(
						'required' => true
					)
				),
				'city' => array(
					'options' => array(
						'required' => true
					)
				),
				'telephone' => array(
					'options' => array(
						'required' => true,
					)
				),
				'mobile_telephone' => array(
					'options' => array(
						'label' => 'Mobile telephone'
					)
				),
				'emailaddress' => array(
					'options' => array(
						'required' => true,
						'reqex' => 'email'
					)
				),
				'house_address' => array(
					'options' => array(
						'required' => true,
						'label' => 'House address'
					)
				),
				'house_zipcode' => array(
					'options' => array(
						'required' => true
					)
				),
				'house_city' => array(
					'options' => array(
						'required' => true
					)
				),
				'house_bedrooms' => array(
					'options' => array(
						'required' => true,
						'type' => 'select',
						'options' => array(
							'' => '',
							'1' => '1',
							'2' => '2',
							'3' => '3',
							'4' => '4'
						)
					)
				),
				'house_surface' => array(
					'options' => array(
						'regex' => 'number'
					)
				),
			)
		);

		if (!empty(self::$_customRentalDeclarationForm))
		{
			$addDeclaration['form'] = self::$_customRentalDeclarationForm;
		}

		$addDeclaration['error'] = false;

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_rental_declaration']))
		{
			if ($this->_validateForm($addDeclaration))
			{
				$tmp = $_POST;
				$tmp['name'] = $tmp['last_name'];

				$rs = $this->_fetchData(self::ACTION_ADD_RENTAL_DECLARATION, $tmp);

				if (!empty($rs['result']))
				{
					$addDeclaration['success'] = true;
					$addDeclaration['filename'] = $rs['result'];
				}
			}
		}

		return $addDeclaration;
	}

	private function _addProspect($input)
	{
		$otherIntel = $this->_getProperty($input);
		
		$prospect = array(
			'form' => array(
				'name' => array(
					'options' => array(
						'required' => true
					)
				),
				'emailaddress' => array(),
				'telephone' => array(),
				'message' => array()
			)
		);
		
		$prospect['error'] = false;
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_prospect']))
		{
			if ($this->_validateForm($prospect))
			{
				unset($_POST['add_prospect']);
				
				$id = 0;
				if ($otherIntel['property'] !== null)
				{
					$id = $otherIntel['property']['house_id'];
				}
				
				$rs = $this->_fetchData(self::ACTION_ADD_PROSPECT, array_merge($_POST, array(
					'house_id' => $id,
				)));
				
				if (!empty($rs['result']))
				{
					$prospect['success'] = true;
				}
			}
		}
		
		return array_merge($otherIntel, array(
			'prospect' => $prospect
		));
	}
	
	private function _validateForm(&$form)
	{
		$error = false;

		foreach ($form['form'] as $name => &$element)
		{
			if (!isset($element['info']))
			{
				$element['info'] = array();
			}

			$element['info']['value'] = @$_POST[$name];

			if (!empty($element['options']['required']) && empty($_POST[$name]))
			{
				if (!isset($element['info']))
				{
					$element['info'] = array();
				}

				if (empty($element['info']['errors']))
				{
					$element['info']['errors'] = 'Field is required';
					$error = true;
				}
			}

			if (!empty($element['options']['regex']))
			{
				$errorMessage = '';

				if (!empty($_POST[$name]))
				{
					$huh = (array) $element['options']['regex'];
					foreach ($huh as $regex)
					{
						switch ($element['options']['regex'])
						{
							case 'email':
								if (!filter_var($_POST[$name], FILTER_VALIDATE_EMAIL))
								{
									$errorMessage = 'Invalid emailaddress';
								}
								break 2;

							case 'number':
								if (!ctype_digit((string) $_POST[$name]))
								{
									$errorMessage = 'Invalid integer';
								}
								break 2;

							case 'date':
								if (!preg_match(self::DATE_FORMAT_REGEX, $_POST[$name]))
								{
									$errorMessage = 'Invalid date format, "' . self::DATE_FORMAT_HUMAN_READABLE . '" required';
								}
								break 2;
						}
					}

					if (!empty($errorMessage))
					{
						if (empty($element['info']['errors']))
						{
							$element['info']['errors'] = $errorMessage;
							$error = true;
						}
					}
				}
			}

			if (!empty($element['options']['type']) && $element['options']['type'] == 'select' && !empty($_POST[$name]) && !isset($element['options']['options'][$_POST[$name]]))
			{
				$element['info']['value'] = '';

				if (empty($element['info']['errors']))
				{
					$element['info']['errors'] = 'Field not present in list';
					$error = true;
				}
			}
		}

		return !$form['error'] = $error;

		foreach ($form['form'] as $name => &$element)
		{
			$element['info']['value'] = @$_POST[$name];

			if (!empty($element['options']['required']) && empty($_POST[$name]))
			{
				if (!isset($element['info']))
				{
					$element['info'] = array();
				}

				if (empty($element['info']['errors']))
				{
					$element['info']['errors'] = 'Field is required';
					$form['error'] = true;
				}
			}

			if (!empty($element['options']['regex']))
			{
				$error = '';

				if (!empty($_POST[$name]))
				{
					$huh = (array) $element['options']['regex'];
					foreach ($huh as $regex)
					{
						switch ($element['options']['regex'])
						{
							case 'email':
								if (!filter_var($_POST[$name], FILTER_VALIDATE_EMAIL))
								{
									$error = 'Invalid email address';
								}
								break 2;

							case 'number':
								if (!ctype_digit((string) $_POST[$name]))
								{
									$error = 'Value must be a number';
								}
								break 2;
						}
					}

					if (!empty($error))
					{
						if (!isset($element['info']))
						{
							$element['info'] = array();
						}

						if (empty($element['info']['errors']))
						{
							$element['info']['errors'] = $error;
							$form['error'] = true;
						}
					}
				}
			}

			if (!empty($element['options']['type']) && !empty($_POST[$name]) && !isset($element['options']['options'][$_POST[$name]]))
			{
				$element['info']['value'] = '';

				if (!isset($element['info']))
				{
					$element['info'] = array();
				}

				if (empty($element['info']['errors']))
				{
					$element['info']['errors'] = 'Field not present in list';
					$form['error'] = true;
				}
			}
		}

		return $form;
	}

	protected function _getAllProperties()
	{
		static $_dataFetched = false;

		if (!$_dataFetched && (self::debug() & self::DEBUG_IGNORE_CACHE
		 || !file_exists($this->_databaseFile)
		 || filemtime($this->_databaseFile) + $this->_cacheTtl < time()
		 || $this->_databaseFileTouched || filesize($this->_databaseFile) == 0))
		{
			$properties = array();
			$currentIds = array();

			if (file_exists($this->_databaseFile) && filesize($this->_databaseFile) > 0)
			{
				$currentData = unserialize(file_get_contents($this->_databaseFile));

				if ($currentData !== false)
				{
					foreach ($currentData['result'] as $house)
					{
						$properties[$house['house_id']] = $house;
						$currentIds[] = $house['house_id'];
					}
				}
			}

			$newProperties = array();
			$newIds = array();

			foreach ($this->_endpoints as $endpoint)
			{
				$source = $endpoint->getSourceIdentifier();
				$result = $endpoint->getProperties('a while back', $currentIds);

				if (isset($result['result']))
				{
					foreach ($result['result']['properties'] as $property)
					{
						$property['source'] = $source;
						$properties[$property['house_id']] = $property;
					}

					$newIds = array_merge($newIds, explode(';', $result['result']['current-properties']));
				}
				else
				{
					throw new Nomis_Api_Exception('Could not fetch data');
				}
			}

			foreach ($properties as $id => $house)
			{
				if (!in_array($id, $newIds))
				{
					unset($properties[$id]);
				}
			}

			$data = array(
				'result' => $properties,
			);

			if (!@file_put_contents($this->_databaseFile, serialize($data)))
			{
				throw new Nomis_Api_Exception('Database not writable');
			}
		}
		else
		{
			$data = unserialize(file_get_contents($this->_databaseFile));
		}

		$rs = array();

		foreach ($data['result'] as $house)
		{
			$house['id'] = $house['house_id'];
			$rs[$house['id']]  = $house;
		}

		$data['result'] = $rs;

		$_dataFetched = true;

		return $data;
	}

	private function _getAllPages()
	{
		if (!file_exists($this->_databasePage)
		 || filemtime($this->_databasePage) + $this->_cacheTtl < time()
		 || $this->_databasePageTouched || filesize($this->_databasePage) == 0)
		{
			$data = $this->_fetchData(self::ACTION_GET_PAGES);

			if (isset($data['result']))
			{
				if (!@file_put_contents($this->_databasePage, serialize($data)))
				{
					throw new Nomis_Api_Exception('Database not writable');
				}

				return $data;
			}
			else
			{
				throw new Nomis_Api_Exception('Could not fetch data');
			}
		}
		else
		{
			return unserialize(file_get_contents($this->_databasePage));
		}
	}

	public function getCompanyEmailaddress()
	{
		$rs = $this->_fetchData(self::ACTION_GET_COMPANY_EMAILADDRESS);

		return $rs['result'];
	}

	public function _fetchData($action, $data = array(), $apiKey = null)
	{
		$data = http_build_query(array_merge($data, array(
			'key' => $apiKey !== null ? $apiKey : $this->_apiKey,
			'lang' => $this->_language,
			'action' => $action,
			'version' => $this->_apiVersion,
			'client-version' => $this->_buildNumber
		)), '', '&');
		
		$urlInfo = parse_url(self::API_HOST);
		
		$fp = @fsockopen($urlInfo['host'], self::API_PORT, $errno, $errstr);
		$buf = '';

		if (!$fp)
		{
			throw new Nomis_Api_Exception('Could not connect to API-server');
		}
		
		@fputs($fp, "POST {$urlInfo['path']} HTTP/1.0\n");
		@fputs($fp, "Host: " . $urlInfo['host'] . "\n");
		@fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
		@fputs($fp, "Content-length: " . strlen($data) . "\n");
		@fputs($fp, "Connection: close\n\n");
		@fputs($fp, $data);

		while (!feof($fp))
		{
			$buf .= fgets($fp, 128);
		}

		fclose($fp);

		if (empty($buf))
		{
			throw new Nomis_Api_Exception('Zero-sized reply');
		}
		else
		{
			preg_match("|^HTTP/[\d\.x]+ (\d+)|", $buf, $m);

			if (isset($m[1]))
			{
				$restype = floor((int) $m[1] / 100);
	            if ($restype == 4 || $restype == 5)
				{
		            throw new Nomis_Api_Exception('Request unsuccessful');
		        }
	        }
	
			list($headers, $body) = preg_split("/(\r?\n){2}/", $buf, 2);
		}
		
		// self::debug(self::DEBUG_PRINT_RESPONSE | self::DEBUG_PRINT_RESPONSE_DIE);
		if (self::debug() & self::DEBUG_PRINT_RESPONSE)
		{
			print_r($body);

			if (self::debug() & self::DEBUG_PRINT_RESPONSE_DIE)
			{
				exit;
			}
		}

		// try to decode response
		if (function_exists('json_decode'))
		{
			$data = json_decode($body, true);
		}
		else
		{
			throw new Nomis_Api_Exception('API: could not process request');
		}

		if (!empty($data['error']) && $action != self::ACTION_CHECK_API_KEY)
		{
			throw new Nomis_Api_Exception('API: ' . $data['error']);
		}

		return $data;
	}
}

class Nomis_View
{
	private $_includeDir;
	private $_template;
	private $_data;
	private $_extraData;

	public function __construct($includeDir, $template, $data = null, $extraData = array())
	{
		$this->_includeDir = $includeDir;
		$this->_template = $template;
		$this->_data = $data;
		$this->_extraData = $extraData;
	}

	public function display()
	{
		$data = $this->_data;
		$templateFile = $this->_includeDir . $this->_template;
		include $templateFile;
	}

	public function __get($key)
	{

	}

	public function __set($key, $value)
	{

	}

	public function getAllCities()
	{
		return empty($this->_extraData['cities']) ? array() : $this->_extraData['cities'];
	}

	public function getAllDistricts()
	{
		return empty($this->_extraData['districts']) ? array() : $this->_extraData['districts'];
	}

	public function getAllCountries()
	{
		return empty($this->_extraData['countries']) ? array() : $this->_extraData['countries'];
	}

	public function getAllHouseTypes()
	{
		return empty($this->_extraData['house_types']) ? array() : $this->_extraData['house_types'];
	}

	public function formText($name, $info = array(), $options = array())
	{
		$label = !empty($options['label']) ? $options['label'] : ucfirst(strtolower(str_replace('_', ' ', $name)));
		ob_start();
		include $this->_includeDir . 'templates/_field.phtml';
		$rs = ob_get_contents();
		ob_end_clean();

		return $rs;
	}

	public static function html($string, $quote_style = ENT_COMPAT, $charset = 'utf-8', $double_encode = true)
	{
		return htmlentities($string, $quote_style, $charset);//, $double_encode);
	}

	public function shorten($haystack, $length = 20, $needle = array('.', '!', '?'), $endString = '...')
    {
		$needle = (array) $needle;
		$i = 0;
		$count = 0;
		$short = '';
		$open = array();
		$continue = true;

		while ($i < strlen($haystack) && $continue)
		{
		    if ($haystack{$i} == '<')
		    {
				$tmp = substr($haystack, $i, strpos($haystack, '>', $i + 1) - $i + 1);

				if ($tmp{1} != '/' && substr($tmp, -2, 1) != '/')
				{
					$open[] = substr($tmp, 1, strlen($tmp) - 2);
				}
				else
				{
					array_pop($open);
				}

				$short .= $tmp;

				$i += strlen($tmp);
		    }
		    elseif (substr($haystack, $i, 7) == '[break]')
		    {
				$continue = false;
		    }
		    elseif (in_array($haystack{$i}, $needle) && $count > $length)
		    {
				$short .= $haystack{$i};
				$continue = false;
		    }
		    else
		    {
				$short .= $haystack{$i};
				++$count;
				++$i;
		    }
		}

		while (count($open) != 0)
		{
		    $short .= '</' . array_pop($open) . '>';
		}

		return $short;
	}

	public static function seo($str, $replace = '-')
	{
		if (is_string($str) && strlen($str) > 0)
		{
			$str = html_entity_decode(preg_replace(
				'~&([a-z])(tilde|elig|zlig|cedil|acute|uml|grave|circ|slash|ring);~i',
				'$1',
				self::html($str)
			), ENT_COMPAT, 'UTF-8');

			return strtolower(str_replace(
				' ',
				$replace,
				trim(preg_replace('~(?:\s|[^a-z0-9_])+~i', ' ', $str))
			));
		}

		return '';
	}
}

class Nomis_Form
{

}

class Nomis_Api_Exception extends Exception {}


interface Nomis_Api_Endpoint
{
	/**
	 * Returns a list of (modified) properties and a list of current properties
	 * @param string ISO 8601 date
	 * @param array list of IDs
	 * @return 	array(
	 * 				'result' => array(
	 * 					'properties' => array(),
	 * 					'current-properties' => '1;2;3;4;5'
	 * 				)
	 * 			)
	 * 			current properties is a list of `house_id`'s
	 */
	public function getProperties($lastModified, array $currentIds);

	/**
	 * Get the source-identifier for this endpoint
	 */
	public function getSourceIdentifier();
	
	/**
	 * Should we do a request based on the current and last modified time
	 * @param The current time
	 * @param Last modification in the database
	 */
	public function isFetchNeeded($currentTime, $lastModified);
}

class Nomis_Api_Endpoint_Nomis implements Nomis_Api_Endpoint
{
	private $_api;
	private $_key;
	private $_ttl;
	
	public function __construct($api, $key)
	{
		$this->_api = $api;
		$this->_key = $key;

		$this->_ttl = $api->getCacheTtl();
	}
	
	public function getSourceIdentifier()
	{
		return $this->_key;
	}
	
	public function isFetchNeeded($currentTime, $lastModified)
	{
		return $lastModified + mt_rand($this->_ttl, $this->_ttl + 100) < $currentTime;
	}
	
	public function getProperties($lastModified, array $currentIds)
	{
		if (!empty($lastModified))
		{
			$lastModifiedInput = array(
				'last_modified' => $lastModified
			);
		}
		else
		{
			$lastModifiedInput = array();
		}
		
		return $this->_api->_fetchData(
				Nomis_Api::ACTION_GET_PROPERTIES,
				array_merge(
						$lastModifiedInput,
						array(
							'current_ids' => implode(';', $currentIds)
						)
						),
				$this->_key
				);
	}
}

/**
 * Database backend powered version of the API
 * 3.4
 * - Nested lot types & lots in a project
 * 
 * 3.3
 * - Search on multiple cities, range is applied to all cities
 * 
 * 3.2
 * - Add a prospect to a property
 * 
 * 3.1.3
 * - Search for rent only
 * 
 * 3.1.2
 * - Get all locations
 * - Only fetch supported languages
 * - Search for location
 * 
 * 3.1.1
 * - Searching for period
 * - [FiXED] Available at does not work properly (other way around)
 * 
 * 3.1.0
 * - Getter for interior-options
 * - Database is now local aware
 * 
 * 3.0.0
 * - Initial build
 * 
 * * INSTALL:
 *  - create database and run App_Nomis_Api_Db::createTables
 *  - create template dir, with templates
 */
class Nomis_Api_Db extends Nomis_Api
{
	const BUILD_NUMBER = '3.1.3';
	const API_VERSION = 3;
	
	const SAFETY_TIME_BUFFER = 3600;
	
	private $_dbOptions;
	private $_connection;
	
	private $_supportLanguages = array('nl', 'en');
	
	public function __construct($apiKey, $dbOptions, $language = 'nl')
	{
		$this->_apiKey = $apiKey;
		$this->_dbOptions = $dbOptions;
		
		$this->setLanguage($language);
		$this->setCacheTtl();
		$this->setRenderMode();
		$this->setPropertiesPerPage();
		$this->setRandomRelatedProperties();
		$this->setIncludeDir();
	}
	
	protected function _connect()
	{
		if (!is_resource($this->_connection))
		{
			$this->_connection = mysql_connect($this->_dbOptions['host'], $this->_dbOptions['username'], $this->_dbOptions['password']);
			mysql_select_db($this->_dbOptions['dbname'], $this->_connection);
			
			mysql_query("SET NAMES 'utf8'");// or die(mysql_error());
		}
	}
	
	public function _getCurrentIds()
	{
		$this->_connect();
		
		$query = mysql_query(sprintf("SELECT nomis_id FROM properties WHERE lang = '%s'", $this->_language), $this->_connection);
		$ids = array();
		while ($p = mysql_fetch_assoc($query))
		{
			$ids[] = $p['nomis_id'];
		}

		return implode(';', $ids);
	}
	
	public function _getLastModified()
	{
		$this->_connect();
		
		$query = mysql_query(sprintf("SELECT UNIX_TIMESTAMP(MAX(last_modified)) AS last_modified FROM properties WHERE lang = '%s'", $this->_language), $this->_connection) or die(mysql_error());
		$result = mysql_fetch_assoc($query);
		
		if ($result['last_modified'] !== null)
		{
			$time = ($result['last_modified']);
		}
		else
		{
			$timezone = date_default_timezone_get();
			date_default_timezone_set('Europe/Paris');
			$time = time() - $this->_cacheTtl - 1;
			date_default_timezone_set($timezone);
		}
		
		return $time;//date('c', $time);
	}
	
	protected function _find($nomisId)
	{
		$this->_connect();
		
		$query = mysql_query(sprintf("SELECT id FROM properties WHERE nomis_id = '%s' AND lang = '%s'", $this->_escape($nomisId), $this->_language), $this->_connection);
		$result = mysql_fetch_assoc($query);
		
		if ($result !== false)
		{
			return $result['id'];
		}
		
		return null;
	}
	
	private $_fields = array(
		'house_id' => 'nomis_id',
		'client_id', 'street',
		'number', 'addition',
		'district', 'location',
		'zipcode', 'city',
		'country', 'lat', 'lng',
		'forrent', 'forrent_type',
		'price', 'price_inc',
		'available_at', 'available_till',
		'min_contract', 'max_contract',
		'forsale', 'rooms',
		'forsale_price', 'interior',
		'bedrooms', 'persons', 'toilets',
		'bathrooms', 'kitchens', 'elevator',
		'flooring', 'surface',
		'kitchen', 'kitchen_fridge',
		'kitchen_freezer', 'kitchen_dishwasher',
		'kitchen_furnace', 'kitchen_oven',
		'kitchen_microwave', 'kitchen_microwave_combi',
		'kitchen_hood', 'house_type',
		'garden', 'balcony',
		'roofterrace', 'parking',
		'description', 'audience',
		'companyhome_audience', 'registration_date',
		'forrent_front_status', 'online_date',
		'internet_connection', 'companyhome_expat_price',
		'companyhome_personnel_price', 'building_type',
		'project_house_id', 'objecttype_house_id'
	);
	
	protected function _parseInputData($property)
	{
		$rs = array();
		$extra = array();
		
		foreach ($property as $index => $value)
		{
			$found = false;
			foreach ($this->_fields as $key => $field)
			{
				$find = $field;
				if (!ctype_digit((string) $key))
				{
					$find = $key;
				}
				
				if ($index == $find)
				{
					$rs[$field] = $value;
					$found = true;
					break;
				}
			}
			
			if (!$found && !in_array($index, array('broker', 'photos')))
			{
				$extra[$index] = $value;
			}
		}
		
		return array_merge($rs, array(
			'house_id' => $property['house_id'], // backwards compatibility
			'photos' => json_encode($property['photos']),
			'broker' => json_encode($property['broker']),
			'extra' => json_encode($extra)
		));
	}
	
	protected function _insert($property)
	{
		$this->_connect();
		
		$tmp = array_merge($this->_parseInputData($property), array(
			'lang' => $this->_language,
		));
		
		$keys = array_keys($tmp);
		$values = array();
		
		foreach ($tmp as $key => $value)
		{
			$values[] = $this->_escape($value);
		}
		
		$keys[] = 'last_modified';
		
		mysql_query("INSERT INTO properties (" . implode(',', $keys) . ") VALUES ('" . implode("','", $values) . "', NOW())", $this->_connection) or die(mysql_error());
	}
	
	protected function _update($id, $property)
	{
		$this->_connect();
		
		$tmp = array_merge($this->_parseInputData($property), array(
			'lang' => $this->_language,
		));
		
		$values = array();
		
		foreach ($tmp as $key => $value)
		{
			$values[] = $key . " = '" . $this->_escape($value) . "'";
		}
		
		$values[] = 'last_modified = NOW()';
		
		mysql_query("UPDATE properties SET " . implode(', ', $values) . " WHERE id = " . $this->_escape($id), $this->_connection) or die(mysql_error());
	}
	
	protected function _delete($ids)
	{
		if (empty($ids))
		{
			return;
		}
		
		$this->_connect();
		
		if (is_array($ids))
		{
			$map = array_filter(array_map(array($this, '_escape'), $ids));
			
			if (empty($map))
			{
				return;
			}
			
			$where = "nomis_id NOT IN ('" . 
				implode("','", $map) . "')";
		}
		else
		{
			$where = "nomis_id = '" . mysql_real_escape_string($ids) . "'";
		}
		
		$where .= sprintf(" AND lang = '%s'", $this->_language);
		
		mysql_query("DELETE FROM properties WHERE " . $where, $this->_connection);
	}
	
	protected function _getAllProperties()
	{
		static $_dataFetched = false;
		
		if (!$_dataFetched)
		{
			$lastModified = $this->_getLastModified();
			
			$timezone = date_default_timezone_get();
			date_default_timezone_set('Europe/Paris');
			$currentTime = time();
			
			if (self::debug() & self::DEBUG_IGNORE_CACHE
			 || $lastModified + $this->_cacheTtl < $currentTime)
			{
				$currentIds = $this->_getCurrentIds();
				
				$lastModifiedInput = array();
				if (!empty($currentIds))
				{
					$lastModifiedInput = array(                    // substract an hour (safety)
						'last_modified' => date('c', $lastModified - self::SAFETY_TIME_BUFFER)
					);
				}
				
				// we can't use this because new need all the info we can get (server-side that is)
				$lastModifiedInput = array();

				$timeLimit = set_time_limit(0);
				$newData = $this->_fetchData(self::ACTION_GET_PROPERTIES, array_merge($lastModifiedInput, array(
					'current_ids' => (string) $currentIds
				)));
				
				$_dataFetched = true;
				
				if (isset($newData['result']))
				{
					foreach ($newData['result']['properties'] as $property)
					{
						// find it
						$id = $this->_find($property['house_id']);
						
						// no match
						if ($id === null)
						{
							// insert
							$this->_insert($property);
						}
						else
						{
							// update
							$this->_update($id, $property);
						}
					}
					
					// delete properties that dont exist on the server
					$this->_delete(explode(';', $newData['result']['current-properties']));
					
					// update last_modified
					mysql_query(sprintf("UPDATE properties SET last_modified = NOW() WHERE lang = '%s'", $this->_language), $this->_connection) or die(mysql_error());
				}
				else
				{
					throw new Nomis_Api_Exception('Could not fetch data');
				}
				
				set_time_limit($timeLimit);
			}
			
			date_default_timezone_set($timezone);
		}
	}
	
	protected function _getCityCoordinates($city)
	{
		$this->_connect();
		
		$query = mysql_query(sprintf("SELECT * FROM city_coordinates WHERE city = '%s' LIMIT 1",
			$this->_escape($city)), $this->_connection) or die(mysql_error());
		
		$p = mysql_fetch_assoc($query);
		
		return $p === false ? null : $p;
	}
	
	protected function _saveCityCoordinates($city, $lat, $lng)
	{
		$this->_connect();
		
		$query = mysql_query(sprintf("INSERT INTO city_coordinates VALUES (NULL, '%s', '%s', '%s')",
			$this->_escape($city),
			$this->_escape($lat),
			$this->_escape($lng)
		), $this->_connection) or die(mysql_error());
		
		return true;
	}
	
	protected function _search($values)
	{
		$this->_getAllProperties();
		$this->_connect();
		

		$search = array();

		$cities = array();
		if (!empty($values['city']))
		{
			$cities = array_map('strtolower', array_map('trim', explode(',', $values['city'])));
		}

		$districts = array();
		if (!empty($values['district']))
		{
			$districts = array_map('strtolower', array_map('trim', explode(',', $values['district'])));
		}

		$countries = array();
		if (!empty($values['country']))
		{
			$countries = array_map('strtolower', array_map('trim', explode(',', $values['country'])));
		}

		$notCountries = array();
		if (!empty($values['not-country']))
		{
			$notCountries = array_map('strtolower', array_map('trim', explode(',', $values['not-country'])));
		}

		$houseTypes = array();
		if (!empty($values['house-types']))
		{
			$houseTypes = array_map('strtolower', array_map('trim', explode(',', $values['house-types'])));
		}

		$notHouseTypes = array();
		if (!empty($values['not-house-types']))
		{
			$notHouseTypes = array_map('strtolower', array_map('trim', explode(',', $values['not-house-types'])));
		}

		$buildingTypes = array();
		if (!empty($values['building-types']))
		{
			$buildingTypes = array_map('strtolower', array_map('trim', explode(',', $values['building-types'])));
		}
		
		$range = null;
		$geo = false;
		if ((!empty($values['geo']) || !empty($values['city'])) && !empty($values['range']))
		{
			$city = false;
			$coordinates = array();
			
			if (!empty($values['geo']))
			{
				list($lat, $lng) = array_map('trim', explode(',', $values['geo']));
				$coordinates[] = array(
					'lat' => (float) $lat,
					'lng' => (float) $lng
				);
			}
			else
			{
				$cities = explode(',', $values['city']);
				
				foreach ($cities as $city)
				{
					// check for cache
					$coor = $this->_getCityCoordinates($city);

					if ($coor === null)
					{
						$xml = file_get_contents('http://maps.google.com/maps/api/geocode/xml?sensor=false&address='.rawurlencode($city) . '+Netherlands');

						$oXml= simplexml_load_string($xml);

						if (!empty($oXml->result))
						{
							$lat = (float) $oXml->result->geometry->location->lat;
							$lng = (float) $oXml->result->geometry->location->lng;
							
							$coordinates[] = array(
								'lat' => $lat,
								'lng' => $lng
							);

							// save to cache
							$this->_saveCityCoordinates($city, $lat, $lng);

							$searchCity = true;
						}
					}
					else
					{
						$searchCity = true;
						
						$coordinates[] = array(
							'lat' => $lat = (float) $coor['lat'],
							'lng' => $lat = (float) $coor['lng']
						);
					}
				}
			}
			
			if (!empty($coordinates))
			{
				if ($searchCity)
				{
					$search['city'] = $values['city'];
					unset($values['city']);
				}
				
				// $range = !empty($values['range']) ? (float) $values['range'] : 0.03;
				$range = !empty($values['range']) ? (int) $values['range'] : 1;
				
				$ranges = array(
					1 => 0.03,
					5 => 0.06,
					10 => 0.15,
					15 => 0.20,
					20 => 0.25,
					25 => 0.30,
					50 => 0.40,
				);
				
				if (!array_key_exists($range, $ranges))
				{
					$range = 1;
				}
				
				$searchAreas = array();
				foreach ($coordinates as $coors)
				{
					$searchAreas[] = array(
						'left' => $coors['lat'] - $ranges[$range],
						'right' => $coors['lat'] + $ranges[$range],
						'top' => $coors['lng'] - $ranges[$range],
						'bottom' => $coors['lng'] + $ranges[$range],
					);
				}
			
				$geo = true;
			}
		}
		
		$where = array();

		foreach ($values as $index => $value)
		{
			switch ($index)
			{
				case 'range':
					if ($geo)// && (empty($house['lat']) || empty($house['lng'])
					 // || (float) $house['lat'] < $left || (float) $house['lat'] > $right
					 // || (float) $house['lng'] < $top || (float) $house['lng'] > $bottom))
					{
						$search['range'] = $range;
						
						$searchAreaWhere = array();
						foreach ($searchAreas as $area)
						{
							$searchAreaWhere[] = sprintf("(lat > %f AND lat < %f AND lng > %f AND lng < %f)",
								$this->_escape($area['left']),
								$this->_escape($area['right']),
								$this->_escape($area['top']),
								$this->_escape($area['bottom'])
							);
						}
						
						$where[] = '(' . implode(' OR ', $searchAreaWhere) . ')'; 
						
						// unset($houses[$id]);
						// continue;
					}
					
					break;
				
				case 'for-sale':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['for-sale'] = $value;
						$where[] = 'forsale = ' . $this->_escape($value);
						continue;
					}
					break;
				
				case 'for-rent':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['for-rent'] = $value;
						$where[] = 'forrent = ' . $this->_escape($value);
						continue;
					}
					break;
				
				case 'min-price':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['min-price'] = $value;
						$where[] = 'price >= ' . $this->_escape($value);
						continue;
					}
					break;
				case 'max-price':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['max-price'] = $value;
						$where[] = 'price <= ' . $this->_escape($value);
						continue;
					}
					break;
				case 'forsale-min-price':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['forsale-min-price'] = $value;
						$where[] = 'forsale_price >= ' . $this->_escape($value);
						continue;
					}
					break;
				case 'forsale-max-price':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['forsale-max-price'] = $value;
						$where[] = 'forsale_price <= ' . $this->_escape($value);
						continue;
					}
					break;
				case 'city':
					if (!empty($value))
					{
						$search['city'] = $value;
						$where[] = "city IN ('" . implode("','", array_map(array($this, '_escape'), $cities)) . "')";
						continue;
					}
					break;
				case 'building-types':
					if (!empty($value))
					{
						$search['building-types'] = $value;
						$where[] = "building_type IN ('" . implode("','", array_map(array($this, '_escape'), $buildingTypes)) . "')";
						continue;
					}
					break;
				case 'country':
					if (!empty($value))
					{
						$search['country'] = $value;
						$where[] = "country IN ('" . implode("','", array_map(array($this, '_escape'), $countries)) . "')";
						continue;
					}
					break;
				case 'not-country':
					if (!empty($value))
					{
						$search['not-country'] = $value;
						$where[] = "country NOT IN ('" . implode("','", array_map(array($this, '_escape'), $notCountries)) . "')";
						continue;
					}
					break;
				case 'district':
					if (!empty($value))
					{
						$search['district'] = $value;
						$where[] = "district IN ('" . implode("','", array_map(array($this, '_escape'), $districts)) . "')";
						continue;
					}
					break;
				case 'house_type':
					if (!empty($value))
					{						
						$search['house_type'] = $value;
						$where[] = "(house_type = '" . $this->_escape($value) . "' OR house_type = '')";
						continue;
					}
					break;
				case 'house-types':
					if (!empty($value))
					{
						$search['house-types'] = $value;
						$where[] = "house_type IN ('" . implode("','", array_map(array($this, '_escape'), $houseTypes)) . "')";
						continue;
					}
					break;
				case 'not-house-types':
					if (!empty($value))
					{
						$search['not-house-types'] = $value;
						$where[] = "house_type NOT IN ('" . implode("','", array_map(array($this, '_escape'), $notHouseTypes)) . "')";
						continue;
					}
					break;
				case 'interior':
					if (!empty($value))
					{
						$search['interior'] = $value;
						$where[] = "interior = '" . $this->_escape($value) . "'";
						continue;
					}
					break;
				
				case 'persons':
					if (!empty($value) && preg_match('/^(\d+)(\+)?$/D', $value, $match))
					{
						$search['persons'] = $value;
						
						$operator = '=';
						if (!empty($match[2]))
						{
							$operator = '>=';
						}
						
						$where[] = 'persons ' . $operator . $this->_escape($match[1]);
					}
					break;
				
				case 'available_at':
					if (!empty($value))
					{
						$search['available_at'] = $value;
						$where[] = "available_at <= '" . date('Y-m-d', strtotime($value)) . "'";
						continue;
					}
					break;
				case 'parking':
					if (!empty($value))
					{
						$search['parking'] = $value;
						$where[] = "(parking = '" . $this->_escape($value) . "' OR parking = '')";
						continue;
					}
					break;
				case 'garden':
					if (!empty($value))
					{
						$search['garden'] = $value;
						$where[] = "(garden = '" . $this->_escape($value) . "' OR garden = '')";
						continue;
					}
					break;
				case 'balcony':
					if (!empty($value))
					{
						$search['balcony'] = $value;
						$where[] = "(balcony = '" . $this->_escape($value) . "' OR balcony = '')";
						continue;
					}
					break;
				case 'elevator':
					if (!empty($value))
					{
						$search['elevator'] = $value;
						$where[] = "elevator = '" . $this->_escape($value) . "'";
						continue;
					}
					break;
				
				case 'location':
					if (!empty($value))
					{
						$search['location'] = $value;
						$where[] = sprintf("location = '%s'", $this->_escape($value));
					}
					break;
				
				case 'min-period':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['min-period'] = $value;
						$where[] = sprintf("min_contract <= %d AND max_contract >= %d", (int) $value, (int) $value);
					}
					break;
				
				case 'companyhome_audience':
					if (!empty($value))
					{
						$tmp = array(
							'companyhome_audience = 1',
							'companyhome_audience = 477'
						);
						
						switch ($value)
						{
							case 'expats':
								$tmp[] = 'companyhome_audience = 475';
								break;
							
							case 'technisch-personeel':
								$tmp[] = 'companyhome_audience = 476';
								break;
						}
						
						$where[] = '(' . implode(' OR ', $tmp) . ')';
					}
					break;
					
				case 'companyhome_expat_price-min':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['min-price'] = $value;
						$where[] = 'companyhome_expat_price >= ' . $this->_escape($value);
						continue;
					}
					break;
				case 'companyhome_expat_price-max':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['max-price'] = $value;
						$where[] = 'companyhome_expat_price <= ' . $this->_escape($value);
						continue;
					}
					break;
				
				case 'companyhome_personnel_price-min':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['min-price'] = $value;
						$where[] = 'companyhome_personnel_price >= ' . $this->_escape($value);
						continue;
					}
					break;
				case 'companyhome_personnel_price-max':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search['max-price'] = $value;
						$where[] = 'companyhome_personnel_price <= ' . $this->_escape($value);
						continue;
					}
					break;
				
				case 'parking':
					if (!empty($value))
					{
						$search['parking'] = $value;
						$where[] = sprintf("parking = '%s'", $this->_escape($value));
					}
					break;
				
				case 'bathrooms':
				case 'kitchens':
				case 'surface':
				case 'bedrooms':
				case 'rooms':
					if (!empty($value) && ctype_digit((string) $value))
					{
						$search[$index] = $value;
						$where[] = $index . ' >= ' . (int) $value;
						continue;
					}
					break;
			}
		}
		
		$order = 'ORDER BY price ASC';
		
		if (!empty($values['order']) && preg_match('~^(price|online_date|city|street|district|bedrooms|forsale_price|available_at|random)((,|=)(asc|desc))?$~i', $values['order'], $match))
		{
			if ($match[1] == 'random')
			{
				$order = 'ORDER BY RAND()';
			}
			else
			{
				$order = 'ORDER BY ' . $match[1] . ' ' . $match[4];
			}

			$search['order'] = $match[1] . (!empty($match[4]) ? '=' . $match[4] : '');
		}
		
		$where[] = sprintf("lang = '%s'", $this->_escape($this->_language));
		
		// exclude lots and lot types
		$where[] = "project_house_id = '' AND objecttype_house_id = ''";
		
		$where = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';
		
		$group = '';
		if (!empty($values['distinct-cities']) && $values['distinct-cities'] === true)
		{
			$group = "GROUP BY city ";
		}
		
		$sql = "SELECT COUNT(*) AS total FROM properties " . $where . ' ' . $group . $order;
		
		$query = mysql_query($sql, $this->_connection) or die(mysql_error());
		$totalResult = mysql_fetch_assoc($query);

		$totalHouses = $totalResult['total'];
		$totalPages = ceil($totalHouses / $this->_propertiesPerPage);

		$page = 1;
		if (!empty($values['page']) && ctype_digit((string) $values['page']) && $values['page'] > 0 && $values['page'] <= $totalPages)
		{
			$search['page'] = $page = $values['page'];
		}
		
		$sql = "SELECT * FROM properties  " . $where . ' ' . $group . $order . ' LIMIT ' . ($page - 1) * $this->_propertiesPerPage . ', ' . $this->_propertiesPerPage;
		
		$query = mysql_query($sql, $this->_connection);
		$result = array();
		while ($p = mysql_fetch_assoc($query))
		{
			$p['photos'] = json_decode($p['photos'], true);
			$p['extra'] = json_decode($p['extra'], true);
			
			$result[$p['id']] = $p;
		}
		
		return $this->_searchResults = array(
			'search' => $search,
			'page' => $page,
			'total_pages' => $totalPages,
			'total' => $totalHouses,
			'result' => $result
		);
	}
	
	private function _getGroupConcat($field)
	{
		static $_groups = array();
		
		if (isset($_groups[$field]))
		{
			return $_group[$field];
		}
		
		$this->_connect();
		
		$query = sprintf("SELECT DISTINCT %s AS `group` FROM properties WHERE lang = '%s'", $this->_escape($field), $this->_escape($this->_language));
		
		$query = mysql_query($query, $this->_connection) or die(mysql_error());
				
		$_group[$field] = array();
		while ($p = mysql_fetch_assoc($query))
		{
			if (!empty($p['group']))
			{
				$_group[$field][] = $p['group'];
			}
		}
		
		natcasesort($_group[$field]);
		
		return $_group[$field];
	}
	
	protected function _getAllCities()
	{
		return $this->_getGroupConcat('city');
	}
	
	protected function _getAllDistricts()
	{
		return $this->_getGroupConcat('district');
	}
	
	protected function _getAllCountries()
	{
		return $this->_getGroupConcat('country');
	}
	
	protected function _getAllHouseTypes()
	{
		return $this->_getGroupConcat('house_type');
	}
	
	protected function _getAllInteriors()
	{
		return $this->_getGroupConcat('interior');
	}
	
	protected function _getAllLocations()
	{
		return $this->_getGroupConcat('location');
	}
	
	protected function _getAllParkings()
	{
		return $this->_getGroupConcat('parking');
	}
	
	protected function _getProperty($input, $noProject = false)
	{
		$properties = $this->_getAllProperties();
		$this->_connect();

		if ($this->_randomRelatedProperties)
		{
			$search = $this->_search(array_merge($input, array(
				'order' => 'random'
			)));
		}
		else
		{
			$search = $this->_search($input);
		}

		$property = null;
		
		if (isset($input['id']))
		{
			if (ctype_digit((string) $input['id']))
			{
				$query = mysql_query(sprintf("SELECT * FROM properties WHERE id = %d AND lang = '%s' LIMIT 1", $this->_escape($input['id']), $this->_language), $this->_connection);
			}
			else
			{
				$query = mysql_query(sprintf("SELECT * FROM properties WHERE nomis_id = '%s' AND lang = '%s' LIMIT 1", $this->_escape($input['id']), $this->_language), $this->_connection);
			}

			$result = null;
			if (is_resource($query))
			{
				$result = mysql_fetch_assoc($query);
			}
			
			if (!empty($result))
			{
				$property = $result;
				// $property['id'] = $input['id'];
				$property['photos'] = json_decode($property['photos'], true);
				$property['broker'] = json_decode($property['broker'], true);
				$property['extra'] = json_decode($property['extra'], true);
				
				// its a project
				if (!empty($property['extra']['project_name']))
				{
					if (!$noProject)
					{
						$property['lot_types'] = array();
						$lotTypes = $this->_lotTypes($property['house_id']);
						
						foreach ($lotTypes as $lotType)
						{
							$tmp = $this->_getProperty(array(
								'id' => $lotType['house_id']
							), true);
							
							$property['lot_types'][] = $tmp['property'];
						}
					}
				}
				
				// lot type
				elseif (!empty($property['project_house_id']) && empty($property['objecttype_house_id']))
				{
					if (!$noProject)
					{
						// fetch project
						$property['project'] = $this->_getProperty(array(
							'id' => $property['project_house_id']
						), true);
					}
					
					$property['lots'] = array();
					$lots = $this->_lots($property['house_id']);
					
					foreach ($lots as $lotType)
					{
						$tmp = $this->_getProperty(array(
							'id' => $lotType['house_id']
						), true);
						
						$property['lots'][] = $tmp['property'];
					}
				}
				
				// lot
				elseif (!empty($property['project_house_id']) && empty($property['objecttype_house_id']))
				{
					// fetch project
					$property['project'] = $this->_getProperty(array(
						'id' => $property['project_house_id']
					), true);
					
					$property['lot_type'] = $this->_getProperty(array(
						'id' => $property['project_objecttype_id']
					), true);
				}
			}
		}

		$this->_propertyInformation = $property;
		return array(
			'search' => $search,
			'property' => $property
		);
	}
	
	private function _lotTypes($projectId)
	{
		$query = mysql_query(sprintf("SELECT * FROM properties WHERE project_house_id = '%s' AND objecttype_house_id = ''", $projectId));
		
		$tmp = array();
		
		if (is_resource($query))
		{
			while ($p = mysql_fetch_assoc($query))
			{
				$tmp[] = $p;
			}
		}
		
		return $tmp;
	}
	
	private function _lots($lotTypeId)
	{
		$query = mysql_query(sprintf("SELECT * FROM properties WHERE project_house_id != '' AND objecttype_house_id = '%s' ORDER BY street ASC, `number` ASC", $lotTypeId));
		
		$tmp = array();
		
		if (is_resource($query))
		{
			while ($p = mysql_fetch_assoc($query))
			{
				$tmp[] = $p;
			}
		}
		
		return $tmp;
	}
	
	public function setLanguage($language = self::DEFAULT_LANGUAGE)
	{
		$this->_language = in_array($language, $this->_supportLanguages) ? $language : self::DEFAULT_LANGUAGE;
		
		return $this;
	}
	
	protected function _escape($s)
	{
		return mysql_real_escape_string($s, $this->_connection);
	}
	
	public function createTables()
	{
		mysql_query("CREATE TABLE `city_coordinates` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `city` varchar(200) CHARACTER SET latin1 DEFAULT '',
		  `lat` varchar(30) CHARACTER SET latin1 DEFAULT '',
		  `lng` varchar(30) CHARACTER SET latin1 DEFAULT '',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		mysql_query("CREATE TABLE `properties` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `lang` varchar(2) DEFAULT 'nl',
		  `nomis_id` varchar(10) NOT NULL DEFAULT '',
		  `house_id` varchar(10) NOT NULL DEFAULT '',
		  `last_modified` datetime DEFAULT NULL,
		  `client_id` int(9) NOT NULL DEFAULT '0',
		  `street` varchar(200) NOT NULL DEFAULT '',
		  `number` varchar(10) NOT NULL DEFAULT '',
		  `addition` varchar(20) DEFAULT '',
		  `zipcode` varchar(20) NOT NULL DEFAULT '',
		  `district` varchar(50) DEFAULT '',
		  `location` varchar(60) DEFAULT '',
		  `city` varchar(50) DEFAULT '',
		  `country` varchar(70) DEFAULT '',
		  `lat` varchar(20) DEFAULT '',
		  `lng` varchar(20) DEFAULT '',
		  `forrent` tinyint(1) DEFAULT '0',
		  `forrent_type` varchar(30) DEFAULT '',
		  `price` int(9) DEFAULT '0',
		  `price_inc` tinyint(1) DEFAULT '0',
		  `available_at` date DEFAULT NULL,
		  `available_till` date DEFAULT NULL,
		  `min_contract` tinyint(2) DEFAULT '0',
		  `max_contract` tinyint(2) DEFAULT '0',
		  `forsale` tinyint(1) DEFAULT '0',
		  `forsale_price` int(9) DEFAULT '0',
		  `interior` varchar(30) DEFAULT '',
		  `bedrooms` int(9) DEFAULT '0',
		  `persons` int(9) DEFAULT '0',
		  `toilets` tinyint(2) DEFAULT '0',
		  `bathrooms` tinyint(2) DEFAULT '0',
		  `rooms` tinyint(9) DEFAULT '0',
		  `elevator` varchar(20) DEFAULT NULL,
		  `flooring` varchar(20) DEFAULT NULL,
		  `surface` int(9) DEFAULT '0',
		  `kitchen` varchar(40) DEFAULT NULL,
		  `kitchens` int(9) DEFAULT '0',
		  `kitchen_fridge` enum('0','1') DEFAULT '0',
		  `kitchen_freezer` enum('0','1') DEFAULT '0',
		  `kitchen_dishwasher` enum('0','1') DEFAULT '0',
		  `kitchen_furnace` varchar(30) DEFAULT NULL,
		  `kitchen_oven` enum('0','1') DEFAULT '0',
		  `kitchen_microwave` enum('0','1') DEFAULT '0',
		  `kitchen_microwave_combi` enum('0','1') DEFAULT '0',
		  `kitchen_hood` enum('0','1') DEFAULT '0',
		  `house_type` varchar(40) DEFAULT NULL,
		  `garden` varchar(40) DEFAULT NULL,
		  `balcony` varchar(40) DEFAULT NULL,
		  `roofterrace` varchar(40) DEFAULT NULL,
		  `parking` varchar(40) DEFAULT NULL,
		  `description` text,
		  `audience` varchar(40) DEFAULT NULL,
		  `companyhome_audience` varchar(40) DEFAULT NULL,
		  `registration_date` datetime DEFAULT NULL,
		  `forrent_front_status` int(9) DEFAULT NULL,
		  `photos` text,
		  `online_date` datetime DEFAULT NULL,
		  `broker` text,
		  `extra` text,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		mysql_query("ALTER TABLE properties add `internet_connection` tinyint(1) unsigned");
		
		mysql_query("ALTER TABLE `properties` CHANGE `nomis_id` `nomis_id` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
		CHANGE `house_id` `house_id` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''");
		
		// 20110816
		mysql_query("ALTER TABLE properties add `companyhome_expat_price` int(9) unsigned");
		mysql_query("ALTER TABLE properties add `companyhome_personnel_price` int(9) unsigned");
		
		// 20100401
		mysql_query("ALTER TABLE properties add `building_type` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER broker");
		
		// 20120508
		mysql_query("ALTER TABLE `properties` ADD `project_house_id` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `building_type` ,
			ADD `objecttype_house_id` VARCHAR( 20 ) NOT NULL DEFAULT '' AFTER `project_house_id` ");
	}
}
