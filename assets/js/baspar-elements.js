/* ============================================================
   Baspar Elements — front-end interactions.
   Self-contained, no jQuery. Re-initialises on Elementor's
   `frontend/element_ready` hooks so widgets work in the editor
   preview too. All selectors are scoped to `.baspar-scope`.
   ============================================================ */
( function () {
	'use strict';

	/* ---- Scroll reveal -------------------------------------------------- */
	function initReveal( root ) {
		var els = ( root || document ).querySelectorAll( '.baspar-reveal:not(.in)' );
		if ( ! els.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			els.forEach( function ( el ) { el.classList.add( 'in' ); } );
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					var d = e.target.getAttribute( 'data-reveal-delay' ) || 0;
					setTimeout( function () { e.target.classList.add( 'in' ); }, parseInt( d, 10 ) );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.15 } );
		els.forEach( function ( el ) { io.observe( el ); } );
	}

	/* ---- Animated counters ---------------------------------------------- */
	function animateCounter( el ) {
		if ( el.dataset.counted ) {
			return;
		}
		el.dataset.counted = '1';
		var target = parseFloat( el.getAttribute( 'data-target' ) || el.textContent ) || 0;
		var dur = parseInt( el.getAttribute( 'data-duration' ) || '1600', 10 );
		var faNum = 'fa' === ( el.getAttribute( 'data-locale' ) || 'fa' );
		var start = null;
		function fmt( n ) {
			var s = Math.round( n ).toLocaleString( faNum ? 'fa-IR' : 'en-US' );
			return s;
		}
		function step( t ) {
			if ( ! start ) { start = t; }
			var p = Math.min( 1, ( t - start ) / dur );
			var eased = 1 - Math.pow( 1 - p, 3 );
			el.textContent = fmt( target * eased );
			if ( p < 1 ) { requestAnimationFrame( step ); }
		}
		requestAnimationFrame( step );
	}

	function initCounters( root ) {
		var nums = ( root || document ).querySelectorAll( '.baspar-scope [data-counter]' );
		if ( ! nums.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			nums.forEach( animateCounter );
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					animateCounter( e.target );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.4 } );
		nums.forEach( function ( el ) { io.observe( el ); } );
	}

	/* ---- Tabs (latest products / generic) ------------------------------- */
	function initTabs( root ) {
		var groups = ( root || document ).querySelectorAll( '.baspar-scope [data-tabs]' );
		groups.forEach( function ( group ) {
			if ( group.dataset.tabsInit ) {
				return;
			}
			group.dataset.tabsInit = '1';
			var tabs = group.querySelectorAll( '[data-tab]' );
			var panels = group.querySelectorAll( '[data-panel]' );
			tabs.forEach( function ( tab ) {
				tab.addEventListener( 'click', function () {
					var id = tab.getAttribute( 'data-tab' );
					tabs.forEach( function ( t ) { t.classList.toggle( 'is-active', t === tab ); } );
					panels.forEach( function ( p ) {
						p.classList.toggle( 'is-active', p.getAttribute( 'data-panel' ) === id );
					} );
				} );
			} );
		} );
	}

	/* ---- FAQ accordion -------------------------------------------------- */
	function initFaq( root ) {
		var items = ( root || document ).querySelectorAll( '.baspar-scope .faq-item' );
		items.forEach( function ( item ) {
			var btn = item.querySelector( '.faq-q' );
			if ( ! btn || btn.dataset.faqInit ) {
				return;
			}
			btn.dataset.faqInit = '1';
			btn.addEventListener( 'click', function () {
				var open = item.classList.contains( 'is-open' );
				var single = item.closest( '[data-faq-single]' );
				if ( single ) {
					single.querySelectorAll( '.faq-item' ).forEach( function ( i ) { i.classList.remove( 'is-open' ); } );
				}
				item.classList.toggle( 'is-open', ! open );
			} );
		} );
	}

	/* ---- Mobile nav toggle ---------------------------------------------- */
	function initNav( root ) {
		var burgers = ( root || document ).querySelectorAll( '.baspar-scope .hamburger' );
		burgers.forEach( function ( b ) {
			if ( b.dataset.navInit ) {
				return;
			}
			b.dataset.navInit = '1';
			b.addEventListener( 'click', function () {
				var header = b.closest( '.baspar-scope' );
				var menu = header ? header.querySelector( '.nav-mobile' ) : null;
				if ( menu ) {
					menu.classList.toggle( 'open' );
				}
			} );
		} );
	}

	/* ---- Floating WhatsApp show-on-scroll ------------------------------- */
	function initFloatWa() {
		var fwa = document.querySelector( '.bspr-fwa' );
		if ( ! fwa || fwa.dataset.init ) {
			return;
		}
		fwa.dataset.init = '1';
		var threshold = parseInt( fwa.getAttribute( 'data-threshold' ) || '400', 10 );
		function onScroll() {
			fwa.classList.toggle( 'in', window.scrollY > threshold );
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	/* ---- Boot ----------------------------------------------------------- */
	function boot( root ) {
		initReveal( root );
		initCounters( root );
		initTabs( root );
		initFaq( root );
		initNav( root );
		initFloatWa();
	}

	document.addEventListener( 'DOMContentLoaded', function () { boot( document ); } );

	// Elementor editor / frontend hooks.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
					boot( $scope && $scope[ 0 ] ? $scope[ 0 ] : document );
				} );
			}
		} );
	}
} )();
