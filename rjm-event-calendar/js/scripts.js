jQuery(document).ready(function($)
{	
	$(".tfdate").datepicker({
    	dateFormat: 'yy-mm-dd',
    	showOn: 'button',
    	buttonImage: rjmTemplateUrl + '/rjm-event-calendar/images/icon-datepicker.png',
    	buttonImageOnly: true,
    	numberOfMonths: 3
    });
});