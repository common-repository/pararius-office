<?php

// For compatibility with qTranslate

function parariusoffice_translate_form($input)
{
	if (preg_match('~\[:[a-zA-Z_]{2,5}\](.*)~u', $input, $matches))
	{
		if (function_exists('qtrans_init'))
		{
			return __($input);
		}
		else
		{
			return __(preg_replace('~\[:[a-zA-Z_]{2,5}\](.*)~', '', $matches[1]), 'parariusoffice');
		}
	}
	else
	{
		return __($input, 'parariusoffice');
	}
}
