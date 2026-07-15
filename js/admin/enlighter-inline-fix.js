(function () {
	'use strict';

	function matchesInlineCodeInsert(content) {
		return /<code\b[^>]*class=["'][^"']*\bEnlighterJSRAW\b/i.test(content);
	}

	function matchesBlockCodeInsert(content) {
		return /<pre\b[^>]*class=["'][^"']*\bEnlighterJSRAW\b/i.test(content);
	}

	function attachEnlighterInlineFix(editor) {
		if (!editor || editor.__enlighterInlineFixAttached) {
			return;
		}

		//Marker to avoid doubling down on events
		editor.__enlighterInlineFixAttached = true;

		//We could have replaced the editor.insertContent method itself,
		//	but so long as we can make do without 
		// 	this sort of public API monkey-patching...

		editor.on('BeforeSetContent', function (event) {
			if (event.selection !== true ||typeof event.content !== 'string') {
				return;
			}

			//If inserting inline contents, Enlighter will add a new paragraph:
			// <code class="EnlighterJSRAW" data-enlighter-language="generic">TEST2</code><p></p>

			// Here [1], they are deciding what tag to use depending 
			// 	on whether it is inline or not,
			// but here [2], they are inserting an empty paragrapth 
			// 	even if it is inline

			// [1] https://github.com/EnlighterJS/Plugin.TinyMCE/blob/b37a3eaf63b57f6234e97ca6b38beff01ba8b91d/src/CodeInsert.js#L16
			//	var tag = (e.data.mode == 'inline' ? 'code' : 'pre');

			// [2] https://github.com/EnlighterJS/Plugin.TinyMCE/blob/b37a3eaf63b57f6234e97ca6b38beff01ba8b91d/src/CodeInsert.js#L63
			// 	editor.insertContent(sp + '<' + tag + ' class="EnlighterJSRAW" data-enlighter-language="' + e.data.lang + '">' + code + '</' + tag + '>' + sp + '<p></p>');

			//We are trying to be as selective as possible
			if (matchesInlineCodeInsert(event.content)) {
				event.content = event.content.replace(
					/<p>\s*<\/p>\s*$/i,
					''
				);
			//We also actually want the final paragraph, 
			// 	but tinyMCE fucks it up, so we add some real text
			} else if (matchesBlockCodeInsert(event.content)) {
				event.content = event.content.replace(
					/<p>\s*<\/p>\s*$/i,
					'<p>...</p>'
				);
			}
		});
	}

	function initialize() {
		if (!window.tinymce) {
			console.warn('No TinyMCE instance found!');
			return;
		}

		// Watch out for other editors
		tinymce.on('AddEditor', function (event) {
			attachEnlighterInlineFix(event.editor);
		});

		// Bind to current ones
		tinymce.each(tinymce.editors || [], function (editor) {
			attachEnlighterInlineFix(editor);
		});
	}

	initialize();
})();