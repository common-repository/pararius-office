<?php

// function to properly display dutch city names
function parariusoffice_format_city($city)
{
	$replacements = array();
	$cityList = include PARARIUSOFFICE_PLUGIN_PATH . '/lib/lists/cities.php';

	foreach ($cityList as $optionName => $values)
	{
		$replacement = get_option('nomis_' . $optionName);

		if (!empty($replacement))
		{
			foreach ($values as $k => $v)
			{
				if (!empty($values[$replacement]))
				{
					$replacements[mb_strtolower($v, 'utf-8')] = $values[$replacement];
				}
			}
		}
	}

	$formattedCity = _parariusoffice_format_city($city);
	$replacedCity = isset($replacements[mb_strtolower($formattedCity, 'utf-8')])
		? $replacements[mb_strtolower($formattedCity, 'utf-8')]
		: $formattedCity;

	// wicked special case...
	$lang = substr(get_locale(), 0, 2);
	if ($lang !== 'nl' && in_array($replacedCity, array('Den Haag', '\'s-Gravenhage')))
	{
		return 'The Hague';
	}
	else
	{
		return $replacedCity;
	}
}

function _parariusoffice_format_city($city)
{
	return preg_replace_callback('/[^^]\b(aan|bij|de|den|en|het|in|op|over|ter|van|s|t)\b/i', function($m)
	{
		return strtolower($m[0]);
	},
	preg_replace_callback('/\b(\w+)\b/', function($word)
	{
		if (mb_substr($word[1], 0, 2) == 'ij')
		{
			$uppercaseLength = 2;
		}
		else
		{
			$uppercaseLength = 1;
		}

		return mb_strtoupper(mb_substr($word[1], 0, $uppercaseLength), 'utf-8') . mb_substr($word[1], $uppercaseLength);
	}, mb_strtolower($city, 'utf-8')));
}
