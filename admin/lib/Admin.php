<?php

include_once PARARIUSOFFICE_PLUGIN_PATH . '/lib/Base.php';
include_once PARARIUSOFFICE_PLUGIN_PATH . '/admin/lib/functions/form_admin.php';

// TODO remove duplication
class ParariusOffice_Base_Admin extends ParariusOffice_Base
{
	protected function _init()
	{
		$this->_setupAjaxHandlers();
		$this->_setupAdminPlugin();
		$lists = $this->_retrieveLists();
		$forms = $this->_retrieveForms();
		$priceRanges = $this->_retrievePriceRanges();

		$this->_storeDefaultsToDatabase($lists, $forms, $priceRanges);
	}

	private function _setupAjaxHandlers()
	{
		$this->_addContactAjaxHandler();
		$this->_addMailAFriendAjaxHandler();
	}

	private function _addContactAjaxHandler()
	{
		// is_user_logged_in is missing when not in AJAX-mode
		if (!defined('DOING_AJAX')) return;

		$thiz = $this;
		$contactAjaxHandler = function() use ($thiz)
		{
			$post = $_POST;
			$response = array();

			$thiz->getApi()->setRenderMode(false);
			$property = $thiz->getApi()->run(array(
				'action' => 'property',
				'id' => $post['id']
			));

			if (empty($property['property']))
			{
				$response['error'] = __('Could not find the property', 'parariusoffice');
			}
			else
			{
				$_POST['add_contact'] = true;
				$_POST['subject'] = 'Interesse in ' . $_POST['id'];
				if (!isset($_POST['telephone'])) $_POST['telephone'] = '';

				$_POST['message'] = 'Ik ben geinteresseerd in woning ' . $_POST['id'] . ' aan de ' .
					parariusoffice_property_title($property['property']) .
					".\n-----------\n\n" . $_POST['message'];

				$apiKey = !empty($property['property']['source']) ? $property['property']['source'] : null;
				$api = $thiz->getApi($apiKey);
				$api->setRenderMode(false);
				$rs = $api->run(array('action' => 'contact'));
				$api->setRenderMode(true);

				if (!empty($rs['contact']['success']))
				{
					$response['message'] = __('Thanks for your contact request, we will be in touch with you as soon as possible!', 'parariusoffice');
				}
				else
				{
					$response['error'] = true;
					$response['messages'] = call_user_func(function($info)
					{
						$errors = array();

						foreach ($info['form'] as $name => $element)
						{
							if (!empty($element['info']['errors']))
							{
								$errors[] = $element['info']['errors'];
							}
						}

						return $errors;
					}, $rs['contact']);
				}

				$thiz->getApi()->setRenderMode(true);
			}

			$_POST = $post; // reset

			header('Content-type: application/json');
			echo json_encode($response);
			exit;
		};

		add_action('wp_ajax_parariusoffice_contact', $contactAjaxHandler);
		add_action('wp_ajax_nopriv_parariusoffice_contact', $contactAjaxHandler);
	}

	private function _addMailAFriendAjaxHandler()
	{
		// AJAX-mode only
		if (!defined('DOING_AJAX')) return;

		$thiz = $this;
		$mailAFriendHandler = function() use ($thiz)
		{
			$response = array();

			$thiz->getApi()->setRenderMode(false);
			$property = $thiz->getApi()->run(array(
				'action' => 'property',
				'id' => $_POST['id']
			));
			$thiz->getApi()->setRenderMode(true);

			$senderName = (@$_POST['sender_name'] ?: __('Somebody', 'parariusoffice'));
			$subject = $senderName . ' ' . __('thought you might be interested in this house', 'parariusoffice');

			$message = __('Dear', 'parariusoffice') . ' ' . $_POST['friend_name'] .
				',<br><br> ' . $senderName . ' (' . $_POST['sender_email'] . ') ' .
				__('thought you might be interested in this house', 'parariusoffice') .
				': <a href="' . parariusoffice_property_link($property['property']) . '">' .
				parariusoffice_property_title($property['property']) .
				'</a><br><br>' . nl2br(@$_POST['message']);

			$headers = 'From: ' . $_POST['sender_name'] . ' <' . $_POST['sender_email'] . '>' .
				"\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8";

			if (@mail($_POST['friend_email'], $subject, $message, $headers))
			{
				$response['message'] = __('You have emailed your friend a link to this house!', 'parariusoffice');
			}
			else
			{
				$response['error'] = true;
				$response['messages'] = array(
					__('We apologize for the inconvenience, but something went wrong. Please try to contact your friend in another way.', 'parariusoffice')
				);
			}

			header('Content-type: application/json');
			echo json_encode($response);
			exit;
		};

		add_action('wp_ajax_parariusoffice_mail_a_friend', $mailAFriendHandler);
		add_action('wp_ajax_nopriv_parariusoffice_mail_a_friend', $mailAFriendHandler);
	}

	private function _setupAdminPlugin()
	{
		$thiz = $this; // 5.3 terror

		add_action('admin_menu', function() use ($thiz)
		{
			$page = add_submenu_page(
				'options-general.php',
				'Pararius Office',
				'Pararius Office',
				'manage_options',
				'parariusoffice/parariusoffice.php',
				array($thiz, 'display')
				);

			add_action('admin_print_scripts-' . $page, function()
			{
				wp_register_script(
						'jquery-easy-list-splitter',
						PARARIUSOFFICE_PLUGIN_URL . '/admin/assets/javascript/jquery.easyListSplitter.js'
						);

				wp_enqueue_script(
						'parariusoffice',
						PARARIUSOFFICE_PLUGIN_URL . '/admin/assets/javascript/parariusoffice.js',
						array(
							'jquery',
							'jquery-ui-core',
							'jquery-ui-tabs',
							'jquery-ui-sortable',
							'jquery-easy-list-splitter',
						)
						);

				wp_enqueue_style(
						'jquery-ui-smoothness',
						PARARIUSOFFICE_PLUGIN_URL . '/admin/assets/stylesheets/jquery-ui/smoothness/jquery-ui.css'
						);

				wp_enqueue_style(
						'jquery-ui-smoothness-theme',
						PARARIUSOFFICE_PLUGIN_URL . '/admin/assets/stylesheets/jquery-ui/smoothness/jquery-ui.theme.css'
						);

				wp_enqueue_style(
						'parariusoffice',
						PARARIUSOFFICE_PLUGIN_URL . '/admin/assets/stylesheets/parariusoffice.css'
						);
			});
		});
	}

	private function _retrieveLists()
	{
		return array(
			'rent' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/rent.php',
			'sale' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/sale.php',
			'bog' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/bog.php',
			'search' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/search.php',
			'cities' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/cities.php',
		);
	}

	private function _retrieveForms()
	{
		return array(
			'request' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/forms/request.php',
			'rental-declaration' => include PARARIUSOFFICE_PLUGIN_PATH . '/lib/forms/rental-declaration.php',
		);
	}

	private function _retrievePriceRanges()
	{
		return array(
			'min-price' => '500,750,1000,1250,1500,2000,2500',
			'max-price' => '750,1000,1250,1500,2000,3000,4000',
			'min-sale-price' => '50000,100000,150000,200000,300000,500000',
			'max-sale-price' => '100000,150000,200000,30000,500000,700000',
		);
	}

	private function _storeDefaultsToDatabase($lists, $forms, $priceRanges)
	{
		if (get_option('nomis_search_min_price_steps') == '') update_option('nomis_search_min_price_steps', $priceRanges['min-price']);
		if (get_option('nomis_search_max_price_steps') == '') update_option('nomis_search_max_price_steps', $priceRanges['max-price']);

		if (get_option('nomis_search_min_price_steps_sale') == '') update_option('nomis_search_min_price_steps_sale', $priceRanges['min-sale-price']);
		if (get_option('nomis_search_max_price_steps_sale') == '') update_option('nomis_search_max_price_steps_sale', $priceRanges['max-sale-price']);

		if (get_option('nomis_quick_search_min_price_steps') == '') update_option('nomis_quick_search_min_price_steps', $priceRanges['max-price']);
		if (get_option('nomis_quick_search_max_price_steps') == '') update_option('nomis_quick_search_max_price_steps', $priceRanges['min-price']);

		if (get_option('nomis_property_layout') == '') update_option('nomis_property_layout', '1');
		if (get_option('nomis_properties_order') == '') update_option('nomis_properties_order', 'price=asc');
		if (get_option('nomis_add_request_form') == '') update_option('nomis_add_request_form', $forms['request']);
		if (get_option('nomis_rental_declaration_form') == '') update_option('nomis_rental_declaration_form', $forms['rental-declaration']);

		$this->_saveDefaultDetails($lists);
		$this->_saveDefaultSearch($lists);
	}

	private function _saveDefaultDetails($lists)
	{
		$this->_saveDefaultRentalDetails($lists['rent']);
		$this->_saveDefaultSaleDetails($lists['sale']);
		$this->_saveDefaultBogDetails($lists['bog']);
		$this->_saveDefaultSearch($lists['search']);
	}

	private function _saveDefaultRentalDetails($list)
	{
		$requiresDetails = array(
			'nomis_property_details',
			'nomis_properties_details',
			'nomis_random_properties_details'
		);

		// If there are no entries in the db for which details, use default array to set in db
		foreach ($requiresDetails as $key => $value)
		{
			if (!get_option($value))
			{
				$tmp = array();
				foreach ($list as $detail => $checked)
				{
					$tmp[$detail] = $checked[$key];
				}
				update_option($value, $tmp);
			}
		}
	}

	private function _saveDefaultSaleDetails($list)
	{
		$requiresSaleDetails = array(
			'nomis_property_sale_details'
		);

		// If there are no entries in the db for the SALE details...
		foreach ($requiresSaleDetails as $key => $value)
		{
			if (!get_option($value))
			{
				$tmp = array();
				foreach ($list as $categoryName => $categoryDetails)
				{
					foreach ($categoryDetails as $detail => $checked)
					{
						$tmp[$categoryName][$detail] = $checked[$key];
					}
				}
				update_option($value, $tmp);
			}
		}
	}

	private function _saveDefaultBogDetails($list)
	{
		$requiresBogDetails = array(
			'nomis_property_bog_details'
		);

		// If there are no entries in the db for the BOG details...
		foreach ($requiresBogDetails as $key => $value)
		{
			if (!get_option($value))
			{
				$tmp = array();
				foreach ($list as $categoryName => $categoryDetails)
				{
					foreach ($categoryDetails as $detail => $checked)
					{
						$tmp[$categoryName][$detail] = $checked[$key];
					}
				}
				update_option($value, $tmp);
			}
		}
	}

	private function _saveDefaultSearch($list)
	{
		$requiresSearchCriteria = array(
			'nomis_quick_search_criteria',
			'nomis_search_criteria',
			'nomis_search_criteria_sale'
		);

		// If there are no entries in db for search criteria, use default array to set them in the db
		foreach ($requiresSearchCriteria as $key => $value)
		{
			if (!get_option($value))
			{
				$tmp = array();
				foreach ($list as $detail => $checked)
				{
					$tmp[$detail] = $checked[$key];
				}
				update_option($value, $tmp);
			}
		}
	}

	public function display()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['parariusoffice-submit']))
		{
			$this->_saveNewData($_POST);
		}

		$detailsOrder = $this->_detailsOrder();
		$saleDetailsOrder = $this->_saleDetailsOrder();
		$bogDetailsOrder = $this->_bogDetailsOrder();
		$searchCriteriaOrder = $this->_searchCriteriaOrder();

		include PARARIUSOFFICE_PLUGIN_PATH . '/admin/templates/layout.phtml';
	}

	private function _detailsOrder()
	{
		$requiresDetails = array(
			'nomis_property_details',
			'nomis_properties_details',
			'nomis_random_properties_details'
		);

		// Build values for sorting of the details (after sorting this is done by javascript)
		$detailsOrder = array();
		foreach ($requiresDetails as $value)
		{
			foreach (get_option($value) as $detail => $checked)
			{
				$detailsOrder[$value][] = $detail;
			}
			$detailsOrder[$value] = implode(',', $detailsOrder[$value]);
		}

		return $detailsOrder;
	}

	private function _saleDetailsOrder()
	{
		$requiresSaleDetails = array(
			'nomis_property_sale_details'
		);

		// Build values for sorting of the SALE details (after sorting this is done by javascript)
		$saleDetailsOrder = array();
		foreach ($requiresSaleDetails as $value)
		{
			foreach (get_option($value) as $categoryName => $categoryDetails)
			{
				foreach ($categoryDetails as $detail => $checked)
				{
					$saleDetailsOrder[$value][] = $detail;
				}
			}
			$saleDetailsOrder[$value] = implode(',', $saleDetailsOrder[$value]);
		}

		return $saleDetailsOrder;
	}

	private function _bogDetailsOrder()
	{
		$requiresBogDetails = array(
			'nomis_property_bog_details'
		);

		// Build values for sorting of the BOG details (after sorting this is done by javascript)
		$bogDetailsOrder = array();
		foreach ($requiresBogDetails as $value)
		{
			foreach (get_option($value) as $categoryName => $categoryDetails)
			{
				foreach ($categoryDetails as $detail => $checked)
				{
					$bogDetailsOrder[$value][] = $detail;
				}
			}
			$bogDetailsOrder[$value] = implode(',', $bogDetailsOrder[$value]);
		}

		return $bogDetailsOrder;
	}

	private function _searchCriteriaOrder()
	{
		$requiresSearchCriteria = array(
			'nomis_quick_search_criteria',
			'nomis_search_criteria',
			'nomis_search_criteria_sale'
		);

		// Build values for sorting of the search criteria (after sorting this is done by javascript)
		$searchCriteriaOrder = array();
		foreach ($requiresSearchCriteria as $value)
		{
			foreach (get_option($value) as $criterium => $checked)
			{
				$searchCriteriaOrder[$value][] = $criterium;
			}
			$searchCriteriaOrder[$value] = implode(',', $searchCriteriaOrder[$value]);
		}

		return $searchCriteriaOrder;
	}

	private function _saveNewData($rawData)
	{
		$currentApiKey = get_option('nomis_api_key');

		if ($currentApiKey != $rawData['api-key'])
		{
			$this->_clearCache();
		}

		update_option('nomis_api_key', trim($rawData['api-key']));

		if (empty($rawData['api-key-valid']))
		{
			return;
		}

		$lists = $this->_retrieveLists();
		$forms = $this->_retrieveForms();

		$this->_saveRentalDetails($lists['rent'], $rawData);
		$this->_saveSaleDetails($lists['sale'], $rawData);
		$this->_saveBogDetails($lists['bog'], $rawData);
		$this->_saveSearchCriteria($lists['search'], $rawData);
		$this->_saveCityReplacements($lists['cities'], $rawData);
		$this->_saveForms($forms, $rawData);
		$this->_writePlainOptions($rawData);
	}

	private function _saveRentalDetails($list, $rawData)
	{
		$requiresDetails = array(
			'nomis_property_details',
			'nomis_properties_details',
			'nomis_random_properties_details'
		);

		foreach ($requiresDetails as $value)
		{
			$details = array();
			if (!empty($rawData[$value . '_order']))
			{
				foreach (explode(',', $rawData[$value . '_order']) as $detail)
				{
					if (array_key_exists($detail, $list))
					{
						$details[$detail] = !empty($rawData[$value . '_' . $detail]) ? 1 : 0;
					}
				}
				update_option($value, $details);
			}
		}
	}

	private function _saveSaleDetails($list, $rawData)
	{
		$requiresSaleDetails = array(
			'nomis_property_sale_details'
		);

		foreach ($requiresSaleDetails as $value)
		{
			if (!empty($rawData[$value . '_order']))
			{
				$insert = array();
				foreach (explode(',', $rawData[$value . '_order']) as $detail)
				{
					foreach ($rawData[$value] as $category => $categoryDetail)
					{
						$insert[$category] = $categoryDetail;
					}
				}
				update_option($value, $insert);
			}
		}
	}

	private function _saveBogDetails($rawData)
	{
		$requiresBogDetails = array(
			'nomis_property_bog_details'
		);

		foreach ($requiresBogDetails as $value)
		{
			if (!empty($rawData[$value . '_order']))
			{
				$insert = array();
				foreach (explode(',', $rawData[$value . '_order']) as $detail)
				{
					foreach ($rawData[$value] as $category => $categoryDetail)
					{
						$insert[$category] = $categoryDetail;
					}
				}
				update_option($value, $insert);
			}
		}
	}

	private function _saveSearchCriteria($list, $rawData)
	{
		$requiresSearchCriteria = array(
			'nomis_quick_search_criteria',
			'nomis_search_criteria',
			'nomis_search_criteria_sale'
		);

		foreach ($requiresSearchCriteria as $value)
		{
			if (!empty($rawData[$value]))
			{
				$criteria = array();
				foreach (explode(',', $rawData[$value]) as $criterium)
				{
					if (array_key_exists($criterium, $list))
					{
						$criteria[$criterium] = !empty($rawData[$value . '_' . $criterium]) ? 1 : 0;
					}
				}
				update_option($value, $criteria);
			}
		}
	}

	private function _saveCityReplacements($list, $rawData)
	{
		foreach ($list as $option => $blackhole)
		{
			update_option('nomis_' . $option, isset($rawData[$option]) ? $rawData[$option] : '');
		}
	}

	private function _saveForms($forms, $rawData)
	{
		$requiresForm = array(
			'nomis_add_request_form',
			'nomis_rental_declaration_form'
		);

		foreach ($requiresForm as $formName)
		{
			$formFields = array();

			if (isset($rawData[$formName . '_name']))
			{
				$i = 0;
				foreach ($rawData[$formName . '_name'] as $fieldName)
				{
					if (empty($fieldName))
					{
						continue;
					}

					if ($rawData[$formName . '_type'][$i] == 'select')
					{
						list($fieldName, $selectOptionsTmp) = explode('::', $fieldName);
						$selectOptionsTmp = explode(',', $selectOptionsTmp);

						$selectOptions = array();
						foreach ($selectOptionsTmp as $selectOption)
						{
							list($optionName, $optionLabel) = explode('=>', $selectOption);
							$selectOptions[$optionName] = $optionLabel;
						}
					}

					$formFields[$fieldName] = array(
						'options' => array(
							'label' => $rawData[$formName . '_label'][$i],
							'required' => $rawData[$formName . '_required'][$i] == '1' ? true : false,
							'type' => $rawData[$formName . '_type'][$i],
							'options' => isset($selectOptions) ? $selectOptions : '',
							'description' => $rawData[$formName . '_description'][$i]
						)
					);

					unset($selectOptions);
					$i ++;
				}
				update_option($formName, $formFields);
			}
		}
	}

	private function _writePlainOptions($rawData)
	{
		$options = array(
			'google_analytics_key', 'property_back', 'property_contact',
			'property_print', 'property_addthis', 'property_mailtofriend',
			'property_googlemaps', 'property_streetview', 'property_contact_email',
			'property_layout', 'property_different_sale_details',

			'search_display_labels', 'search_min_price', 'search_max_price',
			'search_city', 'search_district', 'search_interior',
			'search_bedrooms', 'search_available_at', 'search_min_price_steps',
			'search_max_price_steps', 'search_min_price_steps_sale', 'search_max_price_steps_sale',
			'search_garden', 'search_balcony', 'search_elevator',
			'search_parking',

			'quick_search_action', 'quick_search_display_labels', 'quick_search_min_price',
			'quick_search_max_price', 'quick_search_city', 'quick_search_district',
			'quick_search_interior', 'quick_search_bedrooms', 'quick_search_available_at',
			'quick_search_min_price_steps', 'quick_search_max_price_steps', 'quick_search_garden',
			'quick_search_balcony', 'quick_search_elevator', 'quick_search_parking',

			'random_properties_link', 'random_properties_title', 'random_properties_photo',

			'disable_mobile'
		);

		foreach ($options as $option)
		{
			update_option('nomis_' . $option, isset($rawData[$option]) ? $rawData[$option] : '');
		}

		update_option('nomis_properties_order', $rawData['properties_order_type'] . '=' . $rawData['properties_order_ascdesc']);
	}

	private function _clearCache()
	{
		foreach ($this->getApi()->getDefaultDatabaseFiles() as $cacheFile)
		{
			if (!empty($cacheFile))
			{
				file_put_contents($cacheFile, '');
			}
		}
	}
}
