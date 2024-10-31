<?php

function parariusoffice_number_format($number)
{
	return number_format((float) $number, 0, '', '.');
}
