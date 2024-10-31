<?php

function parariusoffice_searchresults_page()
{
	$id = get_option('nomis_quick_search_action');
	$action = '';

	if (!empty($id))
	{
		$action = get_page_link((int) $id);
	}
	else
	{
		$pages = get_pages(); 

		foreach ($pages as $pageData)
		{
			if (strpos($pageData->post_content, '[parariusoffice-properties'))
			{
				$action = get_page_link($pageData->ID);
			}
		}
	}

	return $action;
}
