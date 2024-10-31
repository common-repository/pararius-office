<?php

return array_map(
		function($value)
		{
			return __($value, 'parariusoffice');
		},
		array(
			'please_enter_your_name' => 'Please enter your name.',
			'please_enter_a_valid_emailadress' => 'Please enter a valid emailaddress.',
			'error_occurred_please_review' => 'Error occurred, please review.',
			'please_enter_a_senders_name' => 'Please enter a senders name.',
			'please_enter_a_valid_emailaddress_for_the_sender' => 'Please enter a valid emailaddress for the sender.',
			'please_enter_a_recipients_name' => 'Please enter a recipients name.',
			'please_enter_a_valid_emailaddress_for_the_recipient' => 'Please enter a valid emailaddress for the recipient.',
		)
		);
