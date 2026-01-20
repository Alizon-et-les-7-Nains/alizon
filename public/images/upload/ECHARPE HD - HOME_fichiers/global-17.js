var ap5GlobalPlugin = {
	debug: false,

	log: function(txt) {
		if (ap5GlobalPlugin.debug) {
			console.log(new Date().toUTCString() + ' - ' + txt);
		}
	},

	displayErrors: function(jsonData) {
		// User errors display
		if (jsonData.hasError) {
			var errors = '';
			for (error in jsonData.errors) {
				errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
			}
			if (!!$.prototype.modal) {
				$('<div class="modal fade"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title text-danger">' + ap5_modalErrorTitle + '</h5></div><div class="modal-body"><p>' + errors + '</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">' + ap5_modalErrorClose + '</button></div></div></div></div>').modal('show');
			} else {
				alert(errors);
			}
		}
	},
};

prestashop.on('handleError', function(event) {
	if (
		typeof(event) == 'object' && typeof(event.eventType) == 'string' && event.eventType == 'addProductToCart'
		&& typeof(event.resp) == 'object' && typeof(event.resp.responseJSON) == 'object'
		&& typeof(event.resp.responseJSON.from_AP5) == 'boolean' && typeof(event.resp.responseJSON.hasError) == 'boolean'
	) {
		ap5GlobalPlugin.displayErrors(event.resp.responseJSON);
	}
});