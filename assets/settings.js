/**
 * global: grecaptcha
 */
var WWLoginRecaptcha = (function() {
	var d, sitekey, invisible, container, ready = 0;

	function update()
	{
		if (ready != 2) {
			console && console.log("Not yet ready: ", ready);
			return;
		}

		var key = sitekey.value.trim();
		var inv = invisible.checked ? 'invisible' : '';
		if (key) {
			var old_key = '';
			var old_inv = '';
			var old = container.querySelector('.g-recaptcha');
			if (old) {
				old_key = old.dataset.sitekey;
				old_inv = 'size' in old.dataset ? old.dataset.size : '';
				old.parentNode.removeChild(old);
			}

			if (key != old_key || inv != old_inv) {
				var el = d.createElement('div');
				el.dataset.sitekey = key;
				el.classList.add('g-recaptcha');
				if (invisible.checked) {
					el.dataset.size = 'invisible';
				}

				el = container.appendChild(el);
				grecaptcha.render(el);
			}
		}
	}

	function callback()
	{
		d         = document;
		sitekey   = d.getElementById('sitekey');
		invisible = d.getElementById('invisible');
		container = d.getElementById('recaptcha-container');

		sitekey.addEventListener('blur', update);
		invisible.addEventListener('change', update);

		var el   = d.createElement('script');
		var lang = d.documentElement.lang || 'en';
		var url  = 'https://www.google.com/recaptcha/api.js?hl=' + lang + '&onload=WWLoginRecaptcha_OnLoad&render=explicit';
		el.setAttribute('src', url);
		el.setAttribute('async', '');
		el.setAttribute('defer', '');
		d.head.appendChild(el);

		++ready;
		if (2 == ready) {
			update();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', callback);
	}
	else {
		callback();
	}

	return {
		onload: function() {
			++ready;
			if (2 == ready) {
				update();
			}
		}
	};
})();

function WWLoginRecaptcha_OnLoad()
{
	WWLoginRecaptcha.onload();
}
