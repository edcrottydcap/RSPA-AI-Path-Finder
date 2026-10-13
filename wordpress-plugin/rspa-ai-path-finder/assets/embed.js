/* RSPA AI Path Finder — host-page side of the embed.
 *
 * 1. Auto-height: the quiz posts {type:"rspa-pathfinder-height", height}
 *    whenever its content changes; this sizes the matching iframe so the
 *    receipt never scrolls inside a box. Matches on the sending window, so
 *    several quizzes per page work.
 * 2. Popup: links with data-rspa-apf-open (button, card, floating launcher)
 *    open the quiz in a modal over the page instead of navigating away.
 *    Without JavaScript they are ordinary links to the quiz page. */
(function () {
	'use strict';

	var modal = null, lastFocus = null;

	function frameFor(source) {
		var frames = document.querySelectorAll('iframe[data-rspa-pathfinder]');
		for (var i = 0; i < frames.length; i++) {
			if (frames[i].contentWindow === source) return frames[i];
		}
		return null;
	}

	window.addEventListener('message', function (e) {
		var d = e.data;
		if (!d || typeof d.type !== 'string') return;
		var frame = frameFor(e.source);
		if (!frame) return;
		if (d.type === 'rspa-pathfinder-height') {
			var h = parseInt(d.height, 10);
			if (h > 0) frame.style.height = Math.min(h + 4, 6000) + 'px';
		} else if (d.type === 'rspa-pathfinder-close' && modal && modal.contains(frame)) {
			close();
		}
	});

	function build() {
		modal = document.createElement('div');
		modal.className = 'rspa-apf-modal';
		modal.setAttribute('role', 'dialog');
		modal.setAttribute('aria-modal', 'true');
		modal.setAttribute('aria-label', 'RSPA AI Path Finder');
		modal.hidden = true;
		modal.innerHTML =
			'<div class="rspa-apf-modal__backdrop" data-close></div>' +
			'<div class="rspa-apf-modal__panel">' +
			'<button type="button" class="rspa-apf-modal__close" data-close aria-label="Close">&times;</button>' +
			'<div class="rspa-apf--embed"><iframe data-rspa-pathfinder title="RSPA AI Path Finder quiz" allow="clipboard-write" style="height:560px"></iframe></div>' +
			'</div>';
		modal.addEventListener('click', function (e) {
			if (e.target.hasAttribute('data-close')) close();
		});
		document.addEventListener('keydown', function (e) {
			if (!modal.hidden && e.key === 'Escape') close();
		});
		document.body.appendChild(modal);
	}

	function open(src) {
		if (!modal) build();
		lastFocus = document.activeElement;
		var frame = modal.querySelector('iframe');
		if (frame.getAttribute('src') !== src) frame.setAttribute('src', src);
		modal.hidden = false;
		document.documentElement.classList.add('rspa-apf-modal-open');
		fitVisible();
		frame.addEventListener('load', function () { try { frame.focus(); } catch (err) {} }, { once: true });
		modal.querySelector('.rspa-apf-modal__close').focus();
	}

	function close() {
		if (!modal || modal.hidden) return;
		modal.hidden = true;
		document.documentElement.classList.remove('rspa-apf-modal-open');
		fitVisible();
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}

	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[data-rspa-apf-open]') : null;
		if (!a || e.metaKey || e.ctrlKey || e.shiftKey || e.button > 0) return; /* let "open in new tab" work */
		e.preventDefault();
		open(a.getAttribute('data-rspa-apf-open'));
	});

	/* Some themes overflow sideways on phones (an over-wide header, say), which
	   stretches the layout viewport past the screen: position:fixed elements
	   then land off-screen and phone media queries stop matching. When that
	   happens, pin the launcher and popup to what's actually visible, and flag
	   small screens with a class instead of relying on the media query. */
	var vv = window.visualViewport, ticking = false;
	function fitVisible() {
		ticking = false;
		var visibleW = vv ? vv.width * vv.scale : window.innerWidth;
		document.documentElement.classList.toggle('rspa-apf-small', (vv ? vv.width : window.innerWidth) <= 480);
		var stretched = vv && window.innerWidth > Math.round(visibleW) + 24; /* +24: ignore a desktop scrollbar */
		var launcher = document.querySelector('.rspa-apf-launcher');
		var r = launcher ? launcher.getBoundingClientRect() : null;
		if (launcher && r.width) { /* 0 while hidden behind the open popup */
			if (stretched) {
				var gap = 12;
				var left = launcher.classList.contains('rspa-apf-launcher--left') ? gap : vv.width - r.width - gap;
				launcher.style.left = (vv.offsetLeft + left) + 'px';
				launcher.style.top = (vv.offsetTop + vv.height - r.height - gap) + 'px';
				launcher.style.right = launcher.style.bottom = 'auto';
			} else {
				launcher.style.left = launcher.style.top = launcher.style.right = launcher.style.bottom = '';
			}
		}
		if (modal) {
			var ms = modal.style;
			if (stretched) {
				ms.left = vv.offsetLeft + 'px'; ms.top = vv.offsetTop + 'px';
				ms.width = vv.width + 'px'; ms.height = vv.height + 'px';
				ms.right = ms.bottom = 'auto';
			} else {
				ms.left = ms.top = ms.width = ms.height = ms.right = ms.bottom = '';
			}
		}
	}
	function queueFit() { if (!ticking) { ticking = true; requestAnimationFrame(fitVisible); } }
	if (vv) { vv.addEventListener('resize', queueFit); vv.addEventListener('scroll', queueFit); }
	window.addEventListener('resize', queueFit);
	window.addEventListener('scroll', queueFit, { passive: true });
	window.addEventListener('load', queueFit);

	/* A shared result link (?r=201i) landing on a page whose only quiz is a
	   button/card/launcher: pop the receipt open straight away. */
	function autoOpen() {
		if (!/[?&]r=(101|102|201|301)/.test(location.search)) return;
		if (document.querySelector('.rspa-apf--embed iframe[data-rspa-pathfinder]')) return;
		var a = document.querySelector('a[data-rspa-apf-open]');
		if (a) open(a.getAttribute('data-rspa-apf-open'));
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', autoOpen);
	else autoOpen();
	queueFit();
})();
