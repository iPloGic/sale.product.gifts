(function() {
	'use strict';

	if (!!window.JCSaleProductGiftsComponent)
		return;

	window.JCSaleProductGiftsComponent = function(params) {
		this.siteId = params.siteId || '';
		this.template = params.template || '';
		this.componentPath = params.componentPath || '';
		this.parameters = params.parameters || '';
		this.activator = params.activator;
		this.offer_id_attr = params.offer_id_attr;
		this.container = ".ipl-spg-component-wrapper";
		this.loader = params.loader;

		let obj = this;

		BX.ready(function(){

			BX.bindDelegate(
				document.querySelector(obj.container),
				'click',
				{class: 'ipl-spg-product'},
				function () {
					obj.changeGifts(this);
				}
			);

			BX.bindDelegate(
				document.body,
				'click',
				{ class: obj.activator },
				function() {
					obj.sendRequest('refresh', this.getAttribute(obj.offer_id_attr));
				}
			);

			obj.sendRequest('refresh', obj.parameters.PRODUCT_ID);

		});
	};

	window.JCSaleProductGiftsComponent.prototype.sendRequest = function(action, id, target = this.container) {

		let data = {
			action: action,
			product_id: id,
			siteId: this.siteId,
			template: this.template,
			parameters: this.parameters,
		};

		let cont = document.querySelector(target);

		cont.innerHTML = this.loader;

		BX.ajax.loadJSON(
			this.componentPath + '/ajax.php' +
				(document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
			data,
			function(res) {
				if (res.redirect !== undefined) {
					window.location.href = window.location.origin + res.redirect;
				}
				else {
					if (res.html === undefined) {
						BX.cleanNode(cont);
					}
					cont.innerHTML = res.html;
				}
			}
		);
	}

	window.JCSaleProductGiftsComponent.prototype.changeGifts = function(el) {
		let parent = el.parentNode;
		let elements = parent.children;
		for (let element of elements) {
			element.classList.remove("ipl-spg-product-checked");
		}
		el.classList.add("ipl-spg-product-checked");
		let id = el.getAttribute("data-id");
		/*
		 some custom actions when changing a gift
		 */
	}

	window.JCSaleProductGiftsComponent.prototype.getGiftId = function() {
		let g = document.querySelector(this.container + " .ipl-spg-product-checked");
		if(g !== null) {
			return document.querySelector(this.container + " .ipl-spg-product-checked").getAttribute("data-id");
		}
		else {
			return null;
		}
	}

})();