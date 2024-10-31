<?php

function parariusoffice_boolean($val)
{
	if ($val == '1')
	{
		return __('Yes', 'parariusoffice');
	}
	else
	{
		return __('No', 'parariusoffice');
	}
}
