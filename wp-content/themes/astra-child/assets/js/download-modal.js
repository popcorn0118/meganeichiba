(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {

		var wrappers = document.querySelectorAll('.download');

		wrappers.forEach(function (wrapper) {

			var objectId  = wrapper.getAttribute('data-object-id');
			var trigger   = wrapper.querySelector('.download__trigger');
			var modal     = wrapper.querySelector('.download-modal');
			var overlay   = wrapper.querySelector('.download-modal__overlay');
			var closeBtn  = wrapper.querySelector('.download-modal__close');
			var form      = wrapper.querySelector('.download-modal__form');
			var input     = wrapper.querySelector('.download-modal__input');
			var errorMsg  = wrapper.querySelector('.download-modal__error');
			var submitBtn = wrapper.querySelector('.download-modal__submit');

			if (!trigger || !modal) {
				return;
			}

			function openModal() {
				modal.classList.add('is-active');
				modal.setAttribute('aria-hidden', 'false');
				document.body.classList.add('download-modal-open');

				if (errorMsg) {
					errorMsg.hidden = true;
				}

				if (input) {
					input.value = '';
					input.focus();
				}
			}

			function closeModal() {
				modal.classList.remove('is-active');
				modal.setAttribute('aria-hidden', 'true');
				document.body.classList.remove('download-modal-open');
			}

			trigger.addEventListener('click', openModal);

			if (closeBtn) {
				closeBtn.addEventListener('click', closeModal);
			}

			if (overlay) {
				overlay.addEventListener('click', closeModal);
			}

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && modal.classList.contains('is-active')) {
					closeModal();
				}
			});

			if (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();

					if (!input || !input.value) {
						return;
					}

					if (submitBtn) {
						submitBtn.disabled = true;
					}

					var formData = new FormData();
					formData.append('action', 'child_verify_download_password');
					formData.append('nonce', ChildDownload.nonce);
					formData.append('object_id', objectId);
					formData.append('password', input.value);

					fetch(ChildDownload.ajax_url, {
						method: 'POST',
						credentials: 'same-origin',
						body: formData
					})
						.then(function (res) {
							return res.json();
						})
						.then(function (res) {
							if (res.success && res.data && res.data.url) {
								window.location.href = res.data.url;
								closeModal();
								return;
							}

							if (errorMsg) {
								errorMsg.textContent = (res.data && res.data.message) || '密鑰錯誤，請重新輸入';
								errorMsg.hidden = false;
							}
						})
						.catch(function () {
							if (errorMsg) {
								errorMsg.textContent = '發生錯誤，請稍後再試';
								errorMsg.hidden = false;
							}
						})
						.finally(function () {
							if (submitBtn) {
								submitBtn.disabled = false;
							}
						});
				});
			}
		});
	});
})();
