(function () {
	'use strict';

	const TARGET_NODE_TYPE_ID = typeof Node !== 'undefined' && typeof Node.ELEMENT_NODE !== 'undefined'
		? Node.ELEMENT_NODE
		: 1;

	tinymce.PluginManager.add( 'abnet_enlighter_delete_code_block',function (editor) {
		var deleteButton = null;

		function maybeEnlighterBlock(node) {
			return node.nodeType === TARGET_NODE_TYPE_ID &&
				node.nodeName.toUpperCase() === 'PRE' &&
				editor.dom.hasClass(
					node,
					'EnlighterJSRAW'
				);
		}

		function findEnlighterBlock() {
			var node = editor.selection.getNode();
			var body = editor.getBody();

			while (node && node !== body) {
				if (maybeEnlighterBlock(node)) {
					return node;
				}

				node = node.parentNode;
			}

			return null;
		}

		function isEditableNode(node) {
			return node.nodeType === TARGET_NODE_TYPE_ID &&
				node.getAttribute('data-mce-bogus') !== '1' &&
				node.getAttribute('contenteditable') !== 'false';
		}

		function findNextEditableNode(block) {
			var node = block.nextSibling;

			while (node) {
				if (isEditableNode(node)) {
					return node;
				}

				node = node.nextSibling;
			}

			return null;
		}

		function removeEnlighterBlock(block) {
			if (!block || !block.parentNode) {
				return;
			}

			editor.undoManager.transact(function () {
				var caretTarget = findNextEditableNode(block);

				//If this is the last element in the document, 
				// 	create a paragraph to host the cursor
				if (!caretTarget) {
					//And add a non breaking space for good measure
					caretTarget = editor.dom.create('p', {}, '\u00a0');
					editor.dom.insertAfter(
						caretTarget,
						block
					);
				}

				editor.dom.remove(block);
				editor.selection.setCursorLocation(caretTarget, 0);
				editor.setDirty(true);
			});

			editor.nodeChanged();
			editor.focus();
		}

		editor.addCommand(
			'RemoveEnlighterBlock',
			function () {
				removeEnlighterBlock(
					findEnlighterBlock()
				);
			}
		);

		editor.addShortcut(
			'ctrl+shift+e',
			'Delete code block',
			'RemoveEnlighterBlock'
		);

		editor.addButton('EnlighterDelete', {
			text: 'Delete code',
			title: 'Delete the selected EnlighterJS code block',
			icon: 'remove',
			disabled: true,

			onPostRender: function () {
				deleteButton = this;
			},

			onclick: function () {
				//Store node in case focus changes occur
				editor.execCommand('RemoveEnlighterBlock');
			}
		});

		editor.on('NodeChange', function (event) {
			if (!deleteButton) {
				return;
			}

			deleteButton.disabled(
				!findEnlighterBlock()
			);
		});
	});
})();