<?php

function parariusoffice_money_format($value, $zeroSubstitute = null)
{
	if ($value == 0 && $zeroSubstitute !== null)
	{
		return $zeroSubstitute;
	}
	else
	{
		return '€' . parariusoffice_number_format($value) . ',-';
	}
}
