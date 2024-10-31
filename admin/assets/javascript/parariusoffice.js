jQuery(function($, undefined) {
	var container = $('#parariusoffice-options');
	
	container.find('.tabs').tabs();
	
	container.find('form').submit(function() {
		$('#form-field-template').remove();
	});
	
	container.find('.remove-form-field').live('click', function() {
		$(this).parent().next().remove();
		$(this).parent().remove();
	});

	container.find('.new-form-field').live('click', function() {
		var fieldTemplate = container.find('#form-field-template').clone();
		var inputs = fieldTemplate.find(':input');
		var clickLink = $(this);
		
		$.each(inputs, function(index, value) {
			$(value).attr('name', $(value).attr('name').replace('formdbname', clickLink.parent().parent().attr('rel')));
		});
		
		$(this).after(fieldTemplate.html());
	});
	
	container.find('.uncheck-all').change(function() {
		if (this.checked) {
			$('input', $(this).parent().parent()).attr('checked', 'checked');
        } else {
            $('input', $(this).parent().parent()).attr('checked', '');
        }
	});

	container.find('#nomis-property-sale-details').sortable({
		handle: '.handle',
		update: function() {
			var order = $(this).find('ul');
			var orders = '';
			
			order.each(function() {
				orders += $(this).sortable('serialize').split(/\[\]=[0-9]+&*/g).join(',');
			});

			container.find('#property-sale-details-order').val(orders);
		}
	});
	
	container.find('#nomis-property-bog-details').sortable({
		handle: '.handle',
		update: function() {
			var order = $(this).find('ul');
			var orders = '';
			
			order.each(function() {
				orders += $(this).sortable('serialize').split(/\[\]=[0-9]+&*/g).join(',');
			});

			container.find('#property-bog-details-order').val(orders);
		}
	});
	
	container.find('.details-category ul').sortable({
		handle: '.handle',
		update: function() {
			var order = $(this).parent().parent().find('ul');
			var orders = '';
			
			order.each(function(){
				orders += $(this).sortable('serialize').split(/\[\]=[0-9]+&*/g).join(',');
			});

			container.find('#property-sale-details-order').val(orders);
		}
	});

	container.find("#nomis-property-details").easyListSplitter({
		colNumber: 3
	});
	
	container.find('.property-details').sortable({
		handle: '.handle',
		connectWith: '.property-details',
		update: function() {
			var order = container.find('#nomis-property-details').parent().find('ul');
			var orders = '';
			
			order.each(function(){
				orders += $(this).sortable('serialize').split(/\[\]=[0-9]+&*/g).join(',');
			});

			$('#property-details-order').val(orders);
		}
	});

	container.find("#nomis-properties-details").easyListSplitter({
		colNumber: 3
	});
	
	container.find('.properties-details').sortable({
		handle: '.handle',
		connectWith: '.properties-details',
		update: function()
		{
			var order = $('#nomis-properties-details').parent().find('ul');
			var orders = '';
			
			order.each(function() {
				orders += $(this).sortable('serialize').split(/\[\]=[0-9]+&*/g).join(',');
			});

			container.find('#properties-details-order').val(orders);
		}
	});

	container.find("#nomis-random-properties-details").easyListSplitter({
		colNumber: 3
	});
	
	container.find('.nomis-random-properties-details').sortable({
		handle: '.handle',
		connectWith: '.nomis-random-properties-details',
		update: function() {
			var order = container.find('#nomis-random-properties-details').parent().find('ul');
			var orders = '';
			
			order.each(function() {
				orders += $(this).sortable('serialize').split(/\[\]=[0-9]+&*/g).join(',');
			});

			container.find('#nomis-properties-random-details-order').val(orders);
		}
	});

	$('#quick-search-criteria').sortable({
		handle: '.handle',
		update: function() {
			container.find('#nomis-quick-search-criteria').val($(this).sortable('serialize').substr(30).split(/\[\]=[0-9]+&*/g).join(','));
		}
	});

	container.find('#search-criteria').sortable({
		handle: '.handle',
		update: function() {
			container.find('#nomis-search-criteria').val($(this).sortable('serialize').substr(24).split(/\[\]=[0-9]+&*/g).join(','));
		}
	});

	container.find('#search-criteria-sale').sortable({
		handle: '.handle',
		update: function() {
			container.find('#nomis-search-criteria-sale').val($(this).sortable('serialize').substr(29).split(/\[\]=[0-9]+&*/g).join(','));
		}
	});
});
