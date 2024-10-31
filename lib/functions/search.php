<?php

function parariusoffice_search(array $criteria, $data, $searchType, $allCities, $allDistricts, $allHouseTypes, $allCountries)
{
	$sale = false;
	
	foreach ($criteria as $criterium => $checked)
	{
		if ($checked == 0) continue;

		switch ($criterium)
		{
			case 'minimum_forsale_price':
				$label = __('Minimum price', 'parariusoffice');
				$fieldname = 'forsale-min-price';
				$type = 'select';
				$tmpOptions = explode(',', get_option('nomis_search_min_price_steps_sale'));
				$options = array();
				foreach ($tmpOptions as $tmpOption)
				{
					$options[$tmpOption] = parariusoffice_money_format($tmpOption);
				}
				break;

			case 'maximum_forsale_price':
				$label = __('Maximum price', 'parariusoffice');
				$fieldname = 'forsale-max-price';
				$type = 'select';
				$tmpOptions = explode(',', get_option('nomis_search_max_price_steps_sale'));
				$options = array();
				foreach ($tmpOptions as $tmpOption)
				{
					$options[$tmpOption] = parariusoffice_money_format($tmpOption);
				}
				break;

			case 'maximum_price':
				$label = __('Maximum price', 'parariusoffice');
				$fieldname = 'max-price';
				$type = 'select';
				$tmpOptions = explode(',', get_option($searchType == 'quick' ? 'nomis_quick_search_max_price_steps' : 'nomis_search_max_price_steps'));
				$options = array();
				foreach ($tmpOptions as $tmpOption)
				{
					$options[$tmpOption] = parariusoffice_money_format($tmpOption);
				}
				break;

			case 'minimum_price':
				$label = __('Minimum price', 'parariusoffice');
				$fieldname = 'min-price';
				$type = 'select';
				$tmpOptions = explode(',', get_option($searchType == 'quick' ? 'nomis_quick_search_min_price_steps' : 'nomis_search_min_price_steps'));
				$options = array();
				foreach ($tmpOptions as $tmpOption)
				{
					$options[$tmpOption] = parariusoffice_money_format($tmpOption);
				}
				break;

			case 'city':
				$label = __('City', 'parariusoffice');
				$fieldname = 'city';
				$type = 'select';
				$options = array();
				foreach ($allCities as $city)
				{
					$normalizedCity = parariusoffice_format_city($city);
					$options[$normalizedCity] = $normalizedCity;
				}
				natcasesort($options);
				break;

			case 'country':
				$label = __('Country', 'parariusoffice');
				$fieldname = 'country';
				$type = 'select';
				$options = array();
				$countriesEnum = PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/countries.php';
				foreach ($allCountries as $tmpOption)
				{
					$options[$tmpOption] = __($countriesEnum[$tmpOption], 'parariusoffice');
				}
				break;

			case 'district':
				$label = __('District', 'parariusoffice');
				$fieldname = 'district';
				$type = 'select';
				$options = array();
				foreach ($allDistricts as $city => $tmpOptions)
				{
					foreach ($tmpOptions as $tmpOption)
					{
						$options[parariusoffice_format_city($city)][$tmpOption] = $tmpOption;
					}
				}
				break;

			case 'interior':
				$label = __('Interior', 'parariusoffice');
				$fieldname = 'interior';
				$type = 'select';
				$options = array(
					__('Unfurnished', 'parariusoffice') => __('Unfurnished', 'parariusoffice'),
					__('Furnished', 'parariusoffice') => __('Furnished', 'parariusoffice'),
					__('Bare', 'parariusoffice') => __('Bare', 'parariusoffice')
				);
				break;

			case 'house_type':
				$label = __('House type', 'parariusoffice');
				$fieldname = 'house_type';
				$type = 'select';
				$options = array();
				foreach ($allHouseTypes as $tmpOption)
				{
					$options[$tmpOption] = $tmpOption;
				}
				break;

			case 'bedrooms':
				$label = __('Bedrooms', 'parariusoffice');
				$fieldname = 'bedrooms';
				$type = 'select';
				$options = array(
					1 => '1 +',
					2 => '2 +',
					3 => '3 +',
					4 => '4 +',
					5 => '5 +'
				);
				break;

			case 'surface':
				$label = __('Surface', 'parariusoffice');
				$fieldname = 'surface';
				$type = 'select';
				$options = array(
					25 => '25 m&sup2; +',
					50 => '50 m&sup2; +',
					75 => '75 m&sup2; +',
					100 => '100 m&sup2; +',
					125 => '125 m&sup2; +',
					150 => '150 m&sup2; +'
				);
				break;

			case 'available_at':
				$label = __('Availability', 'parariusoffice');
				$fieldname = 'available_at';
				$type = 'select';
				$options = array(
					date('Y-m-d') => __('Direct', 'parariusoffice'),
					date('Y-m-d', time() + 5356800) => __('Within 2 months', 'parariusoffice')
				);
				break;

			case 'garden':
				$label = __('Garden', 'parariusoffice');
				$fieldname = 'garden';
				$type = 'checkbox';
				break;

			case 'balcony':
				$label = __('Balcony', 'parariusoffice');
				$fieldname = 'balcony';
				$type = 'checkbox';
				break;

			case 'elevator':
				$label = __('Elevator', 'parariusoffice');
				$fieldname = 'elevator';
				$type = 'checkbox';
				break;

			case 'parking':
				$label = __('Parking', 'parariusoffice');
				$fieldname = 'parking';
				$type = 'checkbox';
				break;

			case 'forsale':
				$label = array(
					'0' => __('For rent', 'parariusoffice'),
					'1' => __('For sale', 'parariusoffice')
				);
				$fieldname = 'for-sale';
				$type = 'radio';
				break;

			default:
				continue 2;
		}

		?>
		<div class="criterium <?php echo $fieldname . ' ' . $type . ' ' . ($sale == true ? 'sale' : 'rent'); ?>">
		
		<?php
		switch ($type)
		{
			case 'select':
				if ($searchType == 'quick' && get_option('nomis_quick_search_display_labels') == '1'
				 || $searchType == 'search' && get_option('nomis_search_display_labels') == '1'): ?>
				<label for="<?php echo $fieldname; ?>" class="<?php echo $fieldname . ' ' . $type; ?>"><?php echo $label; ?></label>
				<?php endif; ?>

				<select name="<?php echo $fieldname; ?>" id="<?php echo $fieldname; ?>">
					<option value=""><?php _e('Select', 'parariusoffice');?> <?php echo strtolower($label); ?></option>
					<?php foreach ($options as $optionValue => $optionTitle): ?>
						<?php if (is_array($optionTitle)) : ?>
							<optgroup label="<?php echo $optionValue; ?>">
								<?php foreach ($optionTitle as $key => $innerOpt) : ?>
									<option value="<?php echo $key; ?>"<?php if (isset($data['search'][$fieldname]) && $data['search'][$fieldname] == $key) echo ' selected="selected"'; ?>><?php echo $innerOpt; ?></option>
								<?php endforeach; ?>
						<?php else : ?>
							<option value="<?php echo $optionValue; ?>"<?php if (isset($data['search'][$fieldname]) && $data['search'][$fieldname] == $optionValue) echo ' selected="selected"'; ?>><?php echo $optionTitle; ?></option>
						<?php endif;?>
					<?php endforeach; ?>
				</select>
				<?php
				break;

			case 'checkbox':
				?>
				<input type="checkbox" name="<?php echo $fieldname; ?>" id="<?php echo $fieldname; ?>"<?php if (isset($data['search'][$fieldname]) && $data['search'][$fieldname] == $optionValue) echo ' checked="checked"'; ?> />
				<label for="<?php echo $fieldname; ?>" class="<?php echo $fieldname . ' ' . $type; ?>"><?php echo $label; ?></label>
				<?php
				break;

			case 'radio':
				foreach ($label as $optionKey => $optionLabel):
				?>
				<input value="<?php echo $optionKey; ?>" type="radio" name="<?php echo $fieldname; ?>" id="<?php echo $fieldname . '-' . $optionKey; ?>"<?php if (isset($data['search'][$fieldname]) && $data['search'][$fieldname] == $optionKey) echo ' checked="checked"'; ?> />
				<label for="<?php echo $fieldname . '-' . $optionKey; ?>" class="<?php echo $fieldname . ' ' . $type; ?>"><?php echo $optionLabel; ?></label>
				<?php
				endforeach;
				break;
		}

		?>
		</div>
		<?php
	}
}
