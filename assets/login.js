/**
 * global: grecaptcha
 */
var WWLoginReCaptcha = (function() {
	var d = document;

	function wwr_button()
	{
		return d.getElementById('wp-submit') || d.getElementById('submit');
	}

	var wwr_disable_submit = function() {
		var b = wwr_button();
		b && b.setAttribute('disabled', '');
	};

	var wwr_enable_submit = function() {
		var b = wwr_button();
		b && b.removeAttribute('disabled');
	};

	function callback()
	{
		wwr_disable_submit();
	}

	if (d.readyState === 'loading') {
		d.addEventListener('DOMContentLoaded', callback);
	}
	else {
		callback();
	}

	return {
		enableSubmit:  wwr_enable_submit,
		disableSubmit: wwr_disable_submit
	};
})();

function WWLoginReCaptcha_enableSubmit()
{
	WWLoginReCaptcha.enableSubmit();
}

function WWLoginReCaptcha_disableSubmit()
{
	WWLoginReCaptcha.disableSubmit();
}

function WWLoginReCaptcha_onLoad()
{
	var rc = document.querySelector('.g-recaptcha');
	if (rc && 'size' in rc.dataset && 'invisible' === rc.dataset.size) {
		var r = grecaptcha.execute();
		if ('then' in r && 'function' === typeof r.then) {
			r.then(function() {}, function() {
				var node = document.createElement('div');
				node.dataset.sitekey = rc.dataset.sitekey;
				node.dataset.callback = rc.dataset.callback;
				node.classList.add('g-recaptcha');
				rc.insertAdjacentElement('afterend', node);
				rc.style.display = 'none';
				grecaptcha.render(node);
				var w = rc.querySelector('[name="g-recaptcha-response"]');
				w && w.setAttribute('disabled', '');
			});
		}
	}
}
