<?php

function parariusoffice_property_link($property)
{
	$link = trim(get_bloginfo('url'), '/');

	if (function_exists('qtrans_getLanguage'))
	{
		$link .= '/' . qtrans_getLanguage();
	}

	$city = Nomis_View::seo($property['city']) ?: '-';
	$street = Nomis_View::seo($property['street']) ?: '-';

	$link .= '/property/' . $city . '/' . $street . '/' . $property['id'] . '/';

	return $link;
}
