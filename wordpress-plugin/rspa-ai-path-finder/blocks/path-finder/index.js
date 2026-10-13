/* RSPA AI Path Finder — block editor UI.
 * Plain ES5 + wp.element.createElement so the plugin ships with no build step.
 * The front end is rendered in PHP (includes/embed.php); this file only draws
 * the editor preview and the sidebar controls. */
(function (wp) {
	'use strict';
	var el = wp.element.createElement;
	var useRef = wp.element.useRef;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var cfg = window.rspaApfEditor || {};
	var D = cfg.defaults || {};

	/* Live, auto-sizing preview of the real quiz. */
	function QuizPreview(props) {
		var ref = useRef(null);
		useEffect(function () {
			var frame = ref.current;
			if (!frame) return;
			var win = frame.ownerDocument.defaultView;
			function onMsg(e) {
				if (e.source === frame.contentWindow && e.data && e.data.type === 'rspa-pathfinder-height') {
					frame.style.height = Math.min(parseInt(e.data.height, 10) + 4, 6000) + 'px';
				}
			}
			win.addEventListener('message', onMsg);
			return function () { win.removeEventListener('message', onMsg); };
		}, []);
		return el('iframe', {
			ref: ref,
			src: cfg.previewUrl,
			title: __('RSPA AI Path Finder preview', 'rspa-ai-path-finder'),
			style: { width: '100%', border: 0, display: 'block', height: props.height + 'px' }
		});
	}

	wp.blocks.registerBlockType('rspa/ai-path-finder', {
		edit: function (props) {
			var a = props.attributes;
			var set = props.setAttributes;
			var mode = a.display;
			var linky = mode === 'button' || mode === 'card';
			var label = a.buttonText || D.text;

			var controls = el(be.InspectorControls, null,
				el(c.PanelBody, { title: __('Display', 'rspa-ai-path-finder') },
					el(c.RadioControl, {
						label: __('Show as', 'rspa-ai-path-finder'),
						selected: mode,
						options: [
							{ label: __('Embedded quiz (the whole quiz, inline)', 'rspa-ai-path-finder'), value: 'embed' },
							{ label: __('Promo card with a button', 'rspa-ai-path-finder'), value: 'card' },
							{ label: __('Just a button', 'rspa-ai-path-finder'), value: 'button' }
						],
						onChange: function (v) { set({ display: v }); }
					}),
					linky && el(c.SelectControl, {
						label: __('Button opens the quiz', 'rspa-ai-path-finder'),
						value: a.open,
						options: [
							{ label: __('In a popup on this page', 'rspa-ai-path-finder'), value: 'popup' },
							{ label: __('On its own page', 'rspa-ai-path-finder'), value: 'page' }
						],
						onChange: function (v) { set({ open: v }); }
					}),
					linky && a.open === 'page' && el(c.ToggleControl, {
						label: __('Open in a new tab', 'rspa-ai-path-finder'),
						checked: a.newTab,
						onChange: function (v) { set({ newTab: v }); }
					}),
					mode === 'card' && el(c.TextControl, {
						label: __('Card heading', 'rspa-ai-path-finder'),
						value: a.heading, placeholder: D.heading,
						onChange: function (v) { set({ heading: v }); }
					}),
					mode === 'card' && el(c.TextareaControl, {
						label: __('Card text', 'rspa-ai-path-finder'),
						value: a.blurb, placeholder: D.blurb,
						onChange: function (v) { set({ blurb: v }); }
					}),
					linky && el(c.TextControl, {
						label: __('Button text', 'rspa-ai-path-finder'),
						value: a.buttonText, placeholder: D.text,
						onChange: function (v) { set({ buttonText: v }); }
					}),
					mode === 'embed' && el(c.RangeControl, {
						label: __('Starting height (px)', 'rspa-ai-path-finder'),
						help: __('Only used for the first moment before the quiz sizes itself.', 'rspa-ai-path-finder'),
						value: a.initialHeight,
						min: 300, max: 1200, step: 20,
						onChange: function (v) { set({ initialHeight: v || 640 }); }
					}),
					el('p', null,
						el(c.ExternalLink, { href: cfg.quizUrl }, __('Open the quiz page', 'rspa-ai-path-finder'))
					)
				)
			);

			var button = el('span', { className: 'rspa-apf-button' }, label);
			var body;
			if (mode === 'button') {
				body = el('div', { className: 'rspa-apf rspa-apf--button' }, button);
			} else if (mode === 'card') {
				body = el('div', { className: 'rspa-apf rspa-apf--card' },
					el('p', { className: 'rspa-apf-card__eyebrow' }, 'RSPA AI Path Finder'),
					el('div', { className: 'rspa-apf-card__body' },
						el('div', { className: 'rspa-apf-card__text' },
							el('p', { className: 'rspa-apf-card__heading' }, a.heading || D.heading),
							el('p', { className: 'rspa-apf-card__blurb' }, a.blurb || D.blurb)),
						button));
			} else {
				body = el('div', { className: 'rspa-apf rspa-apf--embed' }, el(QuizPreview, { height: a.initialHeight }));
			}

			return el('div', be.useBlockProps(), controls, body);
		},
		save: function () { return null; } /* rendered in PHP */
	});
})(window.wp);
