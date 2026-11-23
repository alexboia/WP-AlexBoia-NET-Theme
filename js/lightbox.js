/**
 * Image lightbox for single posts (enqueued by functions-lightbox.php as a script module).
 *
 * All the prepared image links of the post content (those with data-pswp-width) form one sequence.
 * The PhotoSwipe core is imported only when the first image is opened.
 * The caption shown under an image is the one of its gallery item, [caption] or figure,
 * falling back to the alternative text of the image.
 */
import PhotoSwipeLightbox from './vendor/photoswipe/photoswipe-lightbox.esm.min.js';

const contentSelector = '.site-content article .entry-content';
const content = document.querySelector(contentSelector);

if (content && content.querySelector('a[data-pswp-width]')) {
	const labels = readLabels();

	const lightbox = new PhotoSwipeLightbox({
		gallery: contentSelector,
		children: 'a[data-pswp-width]',
		pswpModule: () => import('./vendor/photoswipe/photoswipe.esm.min.js'),
		bgOpacity: 0.92,
		showHideAnimationType: 'zoom',
		wheelToZoom: true,
		// At least 24px above and below the image; smaller images are not upscaled, so they get more.
		padding: {top: 24, bottom: 24, left: 0, right: 0},
		closeTitle: labels.closeTitle,
		zoomTitle: labels.zoomTitle,
		arrowPrevTitle: labels.arrowPrevTitle,
		arrowNextTitle: labels.arrowNextTitle,
		errorMsg: labels.errorMsg
	});

	lightbox.on('uiRegister', () => {
		lightbox.pswp.ui.registerElement({
			name: 'abnet-caption',
			order: 9,
			isButton: false,
			appendTo: 'root',
			onInit: (element, pswp) => {
				pswp.on('change', () => {
					const caption = findCaption(pswp.currSlide.data.element);
					element.textContent = caption;
					element.hidden = caption === '';
				});
			}
		});
	});

	// Hide the page scrollbar while the lightbox is open. The page's own width
	// is kept by padding it with the scrollbar width, so the content does not shift.
	lightbox.on('beforeOpen', lockPageScroll);
	lightbox.on('destroy', unlockPageScroll);

	lightbox.init();
}

function lockPageScroll() {
	const root = document.documentElement;
	const scrollbarWidth = window.innerWidth - root.clientWidth;

	root.classList.add('abnet-lightbox-open');
	if (scrollbarWidth > 0) {
		root.style.paddingRight = scrollbarWidth + 'px';
	}
}

function unlockPageScroll() {
	const root = document.documentElement;
	root.classList.remove('abnet-lightbox-open');
	root.style.paddingRight = '';
}

function readLabels() {
	const dataElement = document.getElementById('wp-script-module-data-abnet-lightbox');
	if (!dataElement) {
		return {};
	}

	try {
		return JSON.parse(dataElement.textContent) || {};
	} catch (e) {
		return {};
	}
}

function findCaption(link) {
	if (!link) {
		return '';
	}

	const container = link.closest('.gallery-item, .wp-caption, figure');
	const captionElement = container
		? container.querySelector('.wp-caption-text, .gallery-caption, figcaption')
		: null;

	if (captionElement && captionElement.textContent.trim() !== '') {
		return captionElement.textContent.trim();
	}

	const image = link.querySelector('img');
	return image ? (image.getAttribute('alt') || '').trim() : '';
}
