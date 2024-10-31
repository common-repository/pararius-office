<?php

include_once PARARIUSOFFICE_PLUGIN_PATH . '/lib/Base.php';

class ParariusOffice_Base_Front extends ParariusOffice_Base
{
	protected function _init()
	{
		$this->getApi()->setIncludeDir(PARARIUSOFFICE_PLUGIN_PATH . '/front');
		$this->_setupCustomForms();
		$this->_setupQueryVars();
		$this->_addShortcodes();
		$this->_setupCustomTitle();
		$this->_setupAssets();
		$this->_addAnalytics();
		
		$this->_propertyPrintShortcut();
	}
	
	private function _setupCustomForms()
	{
		$requestForm = get_option('nomis_add_request_form');
		if (!empty($requestForm) && is_array($requestForm))
		{
			$this->getApi()->setCustomRequestForm($requestForm);
		}

		$rentalDeclarationForm = get_option('nomis_rental_declaration_form');
		if (!empty($rentalDeclarationForm) && is_array($rentalDeclarationForm))
		{
			$this->getApi()->setCustomRentalDeclarationForm($rentalDeclarationForm);
		}
	}
	
	private function _setupQueryVars()
	{
		add_filter('query_vars', function($vars)
		{
			$vars[] = 'propertyid';
			return $vars;
		});
	}
	
	private function _setupCustomTitle()
	{
		$thiz = $this;
		
		$customTitle = function($title, $id = null) use ($thiz)
		{
			global $wp_query;

			if (stripos($title, 'property') !== 0
			 // || (get_the_ID() != $id && $id !== null)
			 || empty($wp_query->query_vars['propertyid']))
			{
				return $title;
			}

			$thiz->getApi()->setRenderMode(false);
			$property = $thiz->runApi(array(
				'action' => 'property',
				'id' => $wp_query->query_vars['propertyid']
			));
			$thiz->getApi()->setRenderMode(true);

			if (!empty($property['property']))
			{
				$newTitle = htmlentities(parariusoffice_property_title($property['property']), ENT_QUOTES, 'UTF-8');
			}
			else
			{
				$newTitle = __('Could not find the property', 'parariusoffice');
			}

			return preg_replace('/^property/i', $newTitle, $title);
		};
		
		add_filter('wp_title', $customTitle);
		add_filter('the_title', $customTitle, 10, 10);
	}
	
	private function _addShortcodes()
	{
		add_shortcode('parariusoffice-properties', array($this, 'propertiesShortcode'));
		add_shortcode('parariusoffice-property', array($this, 'propertyShortcode'));
		add_shortcode('parariusoffice-searchform', array($this, 'searchformShortcode'));
		add_shortcode('parariusoffice-add-request', array($this, 'addRequestShortcode'));
		add_shortcode('parariusoffice-add-rental-declaration', array($this, 'addRentalDeclarationShortcode'));
		add_shortcode('parariusoffice-quick-search', array($this, 'quickSearchShortcode'));
		add_shortcode('parariusoffice-random-properties', array($this, 'randomPropertiesShortcode'));
		add_shortcode('parariusoffice-map', array($this, 'propertiesMapShortcode'));
		add_shortcode('parariusoffice-contactform', array($this, 'contactformShortcode'));
	}
	
	public function propertiesShortcode($attr)
	{
		$attr = shortcode_atts(array(
			'num' => 10,
			'template' => null,
			'type' => null,
			'cities' => null,
			'notcountry' => null,
			'country' => null,
			'division' => null,
			'forrent_front_status' => null,
			'notforrent_front_status' => null,
			'order' => null
		), $attr);

		$this->getApi()->setPropertiesPerPage((int) $attr['num']);

		$args = array(
			'action' => 'search',
			'page' => get_query_var('page') ? get_query_var('page') : 1,
			'city' => $attr['cities'],
			'country' => $attr['country'],
			'not-country' => $attr['notcountry'],
			'divisions' => $attr['division'],
			'forrent_front_status' => $attr['forrent_front_status'],
			'not-forrent_front_status' => $attr['notforrent_front_status']
		);

		if ($attr['type'] == 'rent')
		{
			$args['for-rent'] = '1';
		}
		elseif ($attr['type'] == 'sale')
		{
			$args['for-sale'] = '1';
		}

		if ($attr['order'] !== null)
		{
			$args['order'] = $attr['order'];
		}
		else
		{
			$settingsOrder = get_option('nomis_properties_order');

			// `price=asc` === default, don't set it
			if (!empty($settingsOrder) && $settingsOrder !== 'price=asc')
			{
				$args['order'] = $settingsOrder;
			}
		}

		$get = stripslashes_deep($_GET);
		$combinedArgs = array_filter($args) + array_filter($get, function($item) { return $item !== ''; });
		return $this->runApi($combinedArgs, $attr['template'], 'templates/properties.phtml');
	}

	public function propertyShortcode($attr, $content, $shortcodeName, $defaultTemplate = 'templates/property.phtml')
	{
		remove_action('wp_head', 'rel_canonical');

		global $wp_query;

		if (!empty($wp_query->query_vars['propertyid']))
		{
			$attr = shortcode_atts(array(
				'template' => null,
			), $attr);

			$args = array(
				'action' => 'property',
				'id' => $wp_query->query_vars['propertyid'],
			);

			$get = stripslashes_deep($_GET);
			return $this->runApi($args + $get, $attr['template'], $defaultTemplate);
		}
		else
		{
			$wp_query->set_404();
			return '<p>' . __('We are very sorry for the inconvenience. Please contact the owner of this website and we will have it fixed.', 'nomis') . '</p>';
		}
	}
	
	private function _propertyPrintShortcut()
	{
		$thiz = $this;

		add_action('parse_query', function() use ($thiz)
		{
			if (isset($_GET['print-property']))
			{
				echo $thiz->propertyShortcode(array(), '', 'parariusoffice-property', 'templates/property-print.phtml');
				exit; // kill wordpress
			}
		});
	}
	
	public function searchFormShortcode($attr)
	{
		$attr = shortcode_atts(array(
			'template' => null,
			'type' => null
		), $attr);
		
		if ($attr['type'] == 'sale' || !empty($_GET['for-sale']))
		{
			$defaultTemplate = 'templates/searchform-sale.phtml';
		}
		else
		{
			$defaultTemplate = 'templates/searchform.phtml';
		}
		
		$args = array(
			'action' => 'search'
		);
		
		$get = stripslashes_deep($_GET);
		return $this->runApi($args + $get, $attr['template'], $defaultTemplate);
	}
	
	public function addRequestShortcode($attr)
	{
		$args = array(
			'action' => 'add-single-request'
		);

		return $this->runApi($args);
	}
	
	public function addRentalDeclarationShortcode($attr)
	{
		$args = array(
			'action' => 'rental-declaration'
		);

		return $this->runApi($args);
	}
	
	public function quickSearchShortcode($attr)
	{
		$attr = shortcode_atts(array(
			'template' => null
		), $attr);
		
		$args = array(
			'action' => 'search'
		);
		
		return $this->runApi($args, $attr['template'], 'templates/quick-search.phtml');
	}
	
	public function randomPropertiesShortcode($attr)
	{
		$attr = shortcode_atts(array(
			'num' => 3,
			'order' => 'random',
			'template' => null
		), $attr);

		$this->getApi()->setPropertiesPerPage((int) $attr['num']);
		
		$args = array(
			'action' => 'search',
			'order' => $attr['order'],
		);
		
		return $this->runApi($args, $attr['template'], 'templates/random-properties.phtml');
	}
	
	public function propertiesMapShortcode($attr)
	{
		$attr = shortcode_atts(array(
			'template' => null,
		), $attr);

		$this->getApi()->setPropertiesPerPage(999999);
		
		$args = array(
			'action' => 'search'
		);
		
		return $this->runApi($args, $attr['template'], 'templates/properties-map.phtml');
	}
	
	public function contactformShortcode($attr)
	{
		$args = array(
			'action' => 'contact'
		);
		
		return $this->runApi($args);
	}
	
	public function runApi($args, $template = null, $defaultTemplate = null)
	{
		$originalIncludeDir = $this->getApi()->getIncludeDir(get_template_directory());
		$originalTemplate = $this->getApi()->templateFiles[$args['action']];

		if ($template !== null && file_exists(get_template_directory() . '/' . $template))
		{
			$this->getApi()->setIncludeDir(get_template_directory());
			$this->getApi()->templateFiles[$args['action']] = $template;
		}
		elseif ($defaultTemplate !== null)
		{
			$this->getApi()->templateFiles[$args['action']] = $defaultTemplate;
		}
		
		$result = $this->getApi()->run($args);
		
		$this->getApi()->setIncludeDir($originalIncludeDir);
		$this->getApi()->templateFiles[$args['action']] = $originalTemplate;
		
		return $result;
	}
	
	private function _setupAssets()
	{
		add_action('wp_head', function()
		{
			/*
			 * Scripts
			 */
			wp_register_script(
					'google-maps',
					'http://maps.google.com/maps/api/js?sensor=false'
					);

			$scriptNames = array(
				'libs/loader',
				'libs/dom/dom',
				'libs/dom/animate',
				'libs/dom/easings',
				'libs/dom/net',
				'libs/dom/style',
				'libs/dom/utils',
				'libs/magnify',
				'libs/overlay',
				'libs/slider',
				'libs/popup',
				'settings',
			);

			foreach ($scriptNames as $scriptName)
			{
				wp_register_script(
						'mmx-' . $scriptName,
						PARARIUSOFFICE_PLUGIN_URL . '/front/assets/javascript/' . $scriptName . '.js',
						array(),
						ParariusOffice_Base::VERSION
						);
			}

			wp_enqueue_script(
					'parariusoffice',
					PARARIUSOFFICE_PLUGIN_URL . '/front/assets/javascript/parariusoffice.js',
					array_merge(array(
						'jquery',
						'jquery-ui-core',
						'jquery-ui-tabs',
						'google-maps',
					), array_map(function($item) { return 'mmx-' . $item; }, $scriptNames)),
					ParariusOffice_Base::VERSION
					);

			/**
			 * l10n
			 */
			wp_localize_script(
					'parariusoffice',
					'ParariusOfficeL10n',
					require PARARIUSOFFICE_PLUGIN_PATH . '/l10n/javascript.php'
					);

			/*
			 * Styles
			 */
			wp_enqueue_style(
					'parariusoffice',
					PARARIUSOFFICE_PLUGIN_URL . '/front/assets/stylesheets/parariusoffice.css',
					array(),
					ParariusOffice_Base::VERSION
					);

			echo "<script>var ajaxurl = '" . admin_url('admin-ajax.php') . "';</script>";
		});
	}
	
	private function _addAnalytics()
	{
		$key = get_option('nomis_google_analytics_key');
		
		if (!empty($key))
		{
			add_action('wp_head', function() use ($key)
			{
				?>
				<script type="text/javascript">
				var _gaq = [['_setAccount', '<?php echo $key; ?>'], ['_trackPageview']];

				(function() {
					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				}());
				</script>
				<?php
			});
		}
	}
}
