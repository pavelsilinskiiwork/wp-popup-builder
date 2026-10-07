/**
 * Pavel Silinskii Popup Builder — frontend.
 *
 * Vanilla JS, no dependencies. Reads the global `pspbData` from wp_localize_script:
 * { ajaxUrl, nonce, popups: [{ id, trigger, delay, scroll, selector, frequency,
 *   animation, overlay, closeOnOverlay, closeButton }] }
 */
(function () {
	'use strict';

	if (typeof window.pspbData === 'undefined' || !Array.isArray(window.pspbData.popups)) {
		return;
	}

	var data = window.pspbData;
	var popupsById = {};
	var lastFocus = null;
	var converted = {};

	data.popups.forEach(function (popup) {
		popupsById[popup.id] = popup;
	});

	function storageKey(popup) {
		return 'pspb_shown_' + popup.id;
	}

	function pspbShouldShow(popup) {
		if (popup.frequency === 'always') {
			return true;
		}
		try {
			var store = popup.frequency === 'session' ? window.sessionStorage : window.localStorage;
			return !store.getItem(storageKey(popup));
		} catch (e) {
			// Storage blocked (privacy mode, disabled cookies): show rather than never show.
			return true;
		}
	}

	function pspbMarkShown(popup) {
		if (popup.frequency === 'always') {
			return;
		}
		try {
			var store = popup.frequency === 'session' ? window.sessionStorage : window.localStorage;
			store.setItem(storageKey(popup), '1');
		} catch (e) {
			// Ignore — frequency just won't persist.
		}
	}

	function pspbSendAction(popupId, actionType) {
		if (!window.fetch || !window.FormData) {
			return;
		}
		var body = new FormData();
		body.append('action', 'pspb_dismiss');
		body.append('nonce', data.nonce);
		body.append('popup_id', popupId);
		body.append('action_type', actionType);

		try {
			window.fetch(data.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true })
				.catch(function () {});
		} catch (e) {
			// Never break the page over tracking.
		}
	}

	function getPopupEl(popupId) {
		return document.getElementById('pspb-popup-' + popupId);
	}

	function getOverlayEl(popupId) {
		return document.getElementById('pspb-overlay-' + popupId);
	}

	function pspbShowPopup(popup) {
		var el = getPopupEl(popup.id);
		if (!el || el.classList.contains('pspb-active')) {
			return;
		}

		lastFocus = document.activeElement;
		converted[popup.id] = false;

		el.classList.add('pspb-active');
		if (popup.overlay) {
			var overlay = getOverlayEl(popup.id);
			if (overlay) {
				overlay.classList.add('pspb-active');
			}
		}
		el.focus({ preventScroll: true });

		pspbMarkShown(popup);
		pspbSendAction(popup.id, 'shown');
	}

	function pspbDismissPopup(popupId) {
		var el = getPopupEl(popupId);
		if (!el || !el.classList.contains('pspb-active')) {
			return;
		}

		el.classList.remove('pspb-active');
		var overlay = getOverlayEl(popupId);
		if (overlay) {
			overlay.classList.remove('pspb-active');
		}
		if (lastFocus && typeof lastFocus.focus === 'function') {
			lastFocus.focus({ preventScroll: true });
		}

		pspbSendAction(popupId, 'dismissed');
	}

	function markConverted(popupId) {
		if (converted[popupId]) {
			return;
		}
		converted[popupId] = true;
		pspbSendAction(popupId, 'converted');
	}

	function scrollPercent() {
		var max = document.documentElement.scrollHeight - window.innerHeight;
		if (max <= 0) {
			return 100;
		}
		return (window.scrollY / max) * 100;
	}

	function pspbInitTrigger(popup) {
		if (popup.trigger === 'click') {
			// Frequency is checked per click, since the trigger element stays on the page.
			if (!popup.selector) {
				return;
			}
			document.addEventListener('click', function (e) {
				var target;
				try {
					target = e.target.closest(popup.selector);
				} catch (err) {
					return; // Invalid selector saved in admin.
				}
				if (target && pspbShouldShow(popup)) {
					e.preventDefault();
					pspbShowPopup(popup);
				}
			});
			return;
		}

		if (!pspbShouldShow(popup)) {
			return;
		}

		if (popup.trigger === 'delay') {
			window.setTimeout(function () {
				pspbShowPopup(popup);
			}, Math.max(0, popup.delay) * 1000);
		} else if (popup.trigger === 'scroll') {
			var onScroll = function () {
				if (scrollPercent() >= popup.scroll) {
					window.removeEventListener('scroll', onScroll);
					pspbShowPopup(popup);
				}
			};
			window.addEventListener('scroll', onScroll, { passive: true });
			onScroll(); // Short pages that can't scroll, or a reload mid-page.
		} else if (popup.trigger === 'exit_intent') {
			var onLeave = function (e) {
				// Pointer left through the top edge of the viewport (towards tabs / address bar).
				if (!e.relatedTarget && e.clientY <= 0) {
					document.removeEventListener('mouseout', onLeave);
					pspbShowPopup(popup);
				}
			};
			document.addEventListener('mouseout', onLeave);
		}
	}

	// Close buttons, overlay clicks, and conversion tracking.
	document.addEventListener('click', function (e) {
		var dismiss = e.target.closest('[data-pspb-dismiss]');
		if (dismiss) {
			pspbDismissPopup(parseInt(dismiss.getAttribute('data-pspb-dismiss'), 10));
			return;
		}

		var cta = e.target.closest('.pspb-content a[href], .pspb-content button, .pspb-content input[type="submit"]');
		if (cta) {
			var popupEl = cta.closest('.pspb-popup');
			if (popupEl) {
				markConverted(parseInt(popupEl.id.replace('pspb-popup-', ''), 10));
			}
		}
	});

	// Escape closes popups that have a close button or close-on-overlay enabled.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') {
			return;
		}
		Object.keys(popupsById).forEach(function (id) {
			var popup = popupsById[id];
			if (popup.closeButton || popup.closeOnOverlay) {
				pspbDismissPopup(popup.id);
			}
		});
	});

	window.pspbDismissPopup = pspbDismissPopup;

	function init() {
		data.popups.forEach(pspbInitTrigger);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
