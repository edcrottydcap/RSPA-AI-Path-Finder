/* RSPA AI Path Finder — host-page side of the embed.
 *
 * 1. Auto-height: the quiz posts {type:"rspa-pathfinder-height", height}
 *    whenever its content changes; this sizes the matching iframe so the
 *    receipt never scrolls inside a box. Matches on the sending window, so
 *    several quizzes per page work.
 * 2. Popup: links with data-rspa-apf-open (button, card, floating launcher)
 *    open the quiz in a modal over the page instead of navigating away.
 *    Without JavaScript they are ordinary links to the quiz page.
 * 3. Progress: once a visitor has taken the quiz, the floating launcher turns
 *    into their path — "AI 201 path · 2 of 7" — with a panel listing the
 *    receipt's items, a Next-up button, and encouragement. Visiting a page on
 *    the path ticks it off. State lives in this site's localStorage under the
 *    same keys the quiz (served from this site) writes; nothing is sent
 *    anywhere. */
(function () {
	'use strict';

	var PATH_KEY = 'rspa-pathfinder-path';
	var PICK_KEY = 'rspa-pathfinder-picked-up-v2'; /* keys are SKU|URL, one per receipt line */
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
		renderLauncher();
		fitVisible();
		if (lastFocus && lastFocus.focus) lastFocus.focus();
	}

	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a[data-rspa-apf-open]') : null;
		if (!a || e.metaKey || e.ctrlKey || e.shiftKey || e.button > 0) return; /* let "open in new tab" work */
		e.preventDefault();
		open(a.getAttribute('data-rspa-apf-open'));
	});

	/* ————— progress: the floating launcher becomes the visitor's path ————— */

	function read(key) { try { return JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return null; } }
	function write(key, val) { try { localStorage.setItem(key, JSON.stringify(val)); } catch (e) {} }
	function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
	function bareHost(h) { return h.replace(/^www\./, ''); }
	function url(u) { try { return new URL(u, location.href); } catch (e) { return null; } }

	/* Is this item the page being viewed right now? (same site, same path) */
	function isHere(u) {
		var a = url(u);
		if (!a || a.protocol.indexOf('http') !== 0) return false;
		var slash = function (p) { return p.replace(/\/?$/, '/'); };
		return bareHost(a.hostname) === bareHost(location.hostname) && slash(a.pathname) === slash(location.pathname);
	}
	/* Pages on this site open in the same tab; files, other sites, and email in a new one. */
	function opensHere(u) {
		var a = url(u);
		return !!a && a.protocol.indexOf('http') === 0 && bareHost(a.hostname) === bareHost(location.hostname) &&
			!/\.(pdf|docx?|pptx?|zip)$/i.test(a.pathname);
	}

	function progress() {
		var path = read(PATH_KEY);
		if (!path || !path.lines || !path.lines.length) return null;
		var picked = read(PICK_KEY) || {}, n = 0, next = null;
		path.lines.forEach(function (l) { if (picked[l.k]) n++; else if (!next) next = l; });
		return { path: path, picked: picked, n: n, total: path.lines.length, next: next };
	}

	function cheer(p) {
		var need = rewardNeed(p.total);
		if (reward && p.n < need) {
			var togo = need - p.n;
			return (p.n ? 'Nice going! ' : 'Your path is ready. ') + 'Pick up ' + togo + ' more to unlock your RSPA reward.';
		}
		var left = p.total - p.n;
		if (!p.n) return 'Your path is ready. Start with the first item.';
		if (!left) return 'Path complete! You picked up everything on your receipt. Ready for the next level?';
		if (left === 1) return 'One more and your path is complete.';
		if (p.n / p.total >= 0.5) return 'Halfway there. Keep going! ' + left + ' to go.';
		return 'Nice start! ' + left + ' to go.';
	}

	var launcher = null, baseSrc = '', defaultHtml = '', panelOpen = false, justPicked = null, reward = null, unlockedNow = false;

	/* RSPA reward (plugin setting): how many picked-up items unlock it */
	function rewardNeed(total) {
		if (!reward) return Infinity;
		if (reward.unlock === 'receipt') return 0;
		if (reward.unlock === 'count') return Math.min(Math.max(1, +reward.count || 1), total);
		return total;
	}

	function receiptSrc(code) {
		var a = url(baseSrc);
		if (!a) return baseSrc;
		a.searchParams.set('r', code);
		return a.toString();
	}
	function quizSrc() {
		var a = url(baseSrc);
		if (!a) return baseSrc;
		a.searchParams.delete('r');
		return a.toString();
	}

	function renderLauncher() {
		if (!launcher) return;
		var p = progress();
		if (!p) { launcher.innerHTML = defaultHtml; launcher.classList.remove('rspa-apf-launcher--progress'); return; }
		launcher.classList.add('rspa-apf-launcher--progress');
		var pct = Math.round((p.n / p.total) * 100);
		var done = p.n === p.total;
		var h = '';
		h += '<div class="rspa-apf-panel" id="rspa-apf-panel" role="region" aria-label="Your AI path"' + (panelOpen ? '' : ' hidden') + '>';
		h += '<div class="rspa-apf-panel__head"><span>Your AI path · ' + esc(p.path.grade) + ' · ' + esc(p.path.persona) + '</span>';
		h += '<button type="button" class="rspa-apf-panel__x" data-apf="toggle" aria-label="Close">&times;</button></div>';
		h += '<div class="rspa-apf-panel__body">';
		h += '<div class="rspa-apf-bar" role="progressbar" aria-valuemin="0" aria-valuemax="' + p.total + '" aria-valuenow="' + p.n + '"><i style="width:' + pct + '%"></i></div>';
		h += '<p class="rspa-apf-panel__count"><b>' + p.n + ' of ' + p.total + '</b> picked up</p>';
		h += '<p class="rspa-apf-panel__cheer">' + esc(cheer(p)) + '</p>';
		if (reward && p.n >= rewardNeed(p.total)) {
			h += '<div class="rspa-apf-reward"><p class="rspa-apf-reward__h">★ RSPA reward unlocked</p>';
			h += '<p class="rspa-apf-reward__t">' + esc(reward.headline || 'Your RSPA discount') + '</p>';
			h += '<p class="rspa-apf-reward__code"><b>' + esc(reward.code) + '</b><button type="button" data-apf="copy">Copy</button></p>';
			if (reward.details) h += '<p class="rspa-apf-reward__d">' + esc(reward.details) + '</p>';
			if (reward.url) h += '<a class="rspa-apf-reward__cta" href="' + esc(reward.url) + '" target="_blank" rel="noopener">' + esc(reward.cta || 'Redeem now') + ' →</a>';
			h += '</div>';
		}
		if (p.next) {
			h += '<a class="rspa-apf-button rspa-apf-next" data-k="' + esc(p.next.k) + '" href="' + esc(p.next.u) + '"' + (opensHere(p.next.u) ? '' : ' target="_blank" rel="noopener"') + '>Next up: ' + esc(p.next.t) + ' →</a>';
		} else {
			h += '<a class="rspa-apf-button rspa-apf-next" href="' + esc(quizSrc()) + '" data-rspa-apf-open="' + esc(quizSrc()) + '">Retake the quiz →</a>';
		}
		h += '<ol class="rspa-apf-list">';
		p.path.lines.forEach(function (l) {
			var got = !!p.picked[l.k];
			h += '<li' + (got ? ' class="got"' : '') + '><a data-k="' + esc(l.k) + '" href="' + esc(l.u) + '"' + (opensHere(l.u) ? '' : ' target="_blank" rel="noopener"') + '>';
			h += '<span class="rspa-apf-box" aria-hidden="true"></span><span>' + esc(l.t) + (got ? '<span class="rspa-apf-sr"> (picked up)</span>' : '') + '</span></a></li>';
		});
		h += '</ol>';
		h += '<p class="rspa-apf-panel__foot"><a href="' + esc(receiptSrc(p.path.code)) + '" data-rspa-apf-open="' + esc(receiptSrc(p.path.code)) + '">View my receipt</a>';
		if (!done) h += ' · <a href="' + esc(quizSrc()) + '" data-rspa-apf-open="' + esc(quizSrc()) + '">Retake</a>';
		h += '</p></div></div>';
		if (unlockedNow) {
			h += '<p class="rspa-apf-bubble rspa-apf-bubble--reward" role="status">★ RSPA reward unlocked! Open your path for the code.</p>';
		} else if (justPicked) {
			h += '<p class="rspa-apf-bubble" role="status">✓ Picked up: ' + esc(justPicked.t) + ' · ' + p.n + ' of ' + p.total + '</p>';
		}
		h += '<button type="button" class="rspa-apf-button rspa-apf-pill' + (done ? ' rspa-apf-pill--done' : '') + '" data-apf="toggle" aria-expanded="' + panelOpen + '" aria-controls="rspa-apf-panel">';
		h += '<span class="rspa-apf-ring" style="--p:' + pct + '" aria-hidden="true"></span>';
		h += '<span>' + (done ? esc(p.path.grade) + ' path complete ✓' : esc(p.path.grade) + ' path · ' + p.n + ' of ' + p.total) + '</span></button>';
		launcher.innerHTML = h;
		queueFit();
	}

	function initLauncher() {
		launcher = document.querySelector('.rspa-apf-launcher[data-progress]');
		if (!launcher) return;
		var a = launcher.querySelector('a[data-rspa-apf-open]');
		baseSrc = a ? a.getAttribute('data-rspa-apf-open') : '';
		defaultHtml = launcher.innerHTML;
		try { reward = JSON.parse(launcher.getAttribute('data-reward') || 'null'); } catch (e) { reward = null; }

		/* Landing on a page that's on the path ticks it off. */
		var p = progress();
		if (p) {
			var before = p.n;
			p.path.lines.forEach(function (l) {
				if (!p.picked[l.k] && isHere(l.u)) { p.picked[l.k] = Date.now(); justPicked = l; }
			});
			if (justPicked) {
				write(PICK_KEY, p.picked);
				var need = rewardNeed(p.total);
				unlockedNow = !!reward && before < need && before + 1 >= need;
				setTimeout(function () { justPicked = null; unlockedNow = false; renderLauncher(); }, unlockedNow ? 8000 : 5000);
			}
		}

		launcher.addEventListener('click', function (e) {
			var t = e.target.closest ? e.target.closest('[data-apf], a[data-k]') : null;
			if (!t) return;
			if (t.getAttribute('data-apf') === 'copy') {
				var done = function () { t.textContent = 'Copied'; setTimeout(function () { t.textContent = 'Copy'; }, 1600); };
				try { navigator.clipboard.writeText(reward.code).then(done, function () {}); } catch (err) {}
				return;
			}
			if (t.getAttribute('data-apf') === 'toggle') {
				panelOpen = !panelOpen;
				justPicked = null; unlockedNow = false;
				renderLauncher();
				if (panelOpen) { var first = launcher.querySelector('.rspa-apf-next'); if (first) first.focus(); }
				return;
			}
			/* an item link: tick it, then let the browser follow it */
			var picked = read(PICK_KEY) || {};
			picked[t.getAttribute('data-k')] = Date.now();
			write(PICK_KEY, picked);
			if (t.target === '_blank') renderLauncher();
		});
		launcher.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && panelOpen) { panelOpen = false; renderLauncher(); var b = launcher.querySelector('.rspa-apf-pill'); if (b) b.focus(); }
		});
		/* The quiz (in the popup, or another tab) changed the path or ticks. */
		window.addEventListener('storage', function (e) {
			if (e.key === PATH_KEY || e.key === PICK_KEY) renderLauncher();
		});
		renderLauncher();
	}

	/* ————— keep fixed elements on the visible screen —————
	   Some themes overflow sideways on phones (an over-wide header, say), which
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
		var el = document.querySelector('.rspa-apf-launcher');
		var r = el ? el.getBoundingClientRect() : null;
		if (el && r.width) { /* 0 while hidden behind the open popup */
			if (stretched) {
				var gap = 12, cl = el.classList, left, top;
				if (cl.contains('rspa-apf-launcher--bottom-left') || cl.contains('rspa-apf-launcher--top-left')) left = gap;
				else if (cl.contains('rspa-apf-launcher--bottom-center')) left = (vv.width - r.width) / 2;
				else left = vv.width - r.width - gap;
				top = (cl.contains('rspa-apf-launcher--top-left') || cl.contains('rspa-apf-launcher--top-right')) ? gap : vv.height - r.height - gap;
				el.style.left = (vv.offsetLeft + left) + 'px';
				el.style.top = (vv.offsetTop + top) + 'px';
				el.style.right = el.style.bottom = 'auto';
				el.style.transform = 'none';
			} else {
				el.style.left = el.style.top = el.style.right = el.style.bottom = el.style.transform = '';
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
	function start() {
		/* grab the server-rendered opener (it carries the shared ?r=) before the
		   launcher swaps in the visitor's own links */
		var shared = /[?&]r=(101|102|201|301)/.test(location.search) &&
			!document.querySelector('.rspa-apf--embed iframe[data-rspa-pathfinder]') &&
			document.querySelector('a[data-rspa-apf-open]');
		var sharedSrc = shared ? shared.getAttribute('data-rspa-apf-open') : '';
		initLauncher();
		if (sharedSrc) open(sharedSrc);
		queueFit();
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
	else start();
})();
