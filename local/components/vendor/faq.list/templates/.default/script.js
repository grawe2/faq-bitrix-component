BX.ready(function () {
	BX.bindDelegate(document, 'click', {className: 'faq__question'}, function () {
		const item = this;
		if (BX.hasClass(item, '_active')) {
			BX.removeClass(item, '_active');
		} else {
			BX.addClass(item, '_active');
		}
	});
});