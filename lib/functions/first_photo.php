<?php

function parariusoffice_first_photo($property)
{
	if (!empty($property['photos'][0]['small']))
	{
		$photo = $property['photos'][0]['small'];
	}
	else
	{
		$photo = PARARIUSOFFICE_PLUGIN_URL . '/front/assets/images/no_photo-';
					
		if (function_exists('qtrans_getLanguage'))
		{
			$photo .= qtrans_getLanguage();
		}
		else
		{
			$photo .= 'nl';
		}
					
		$photo .= '.png';
	}
	
	return $photo;
}
