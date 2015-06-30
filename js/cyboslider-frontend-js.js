/**
 * jQuery wrapper
 */
( function( $ ) {
	var Slider = new Slider();
	
	/**
	 * handels all slider stuff
	 */
	function Slider() {
		
		var self     = this,
		    duration = 5000,
		    timer;
		
		/**
		 * initiatelize slider
		 */
		self.init = function init() {
			// set the first one as active
			$( '.cyboslider-caption-0' ).addClass( 'active' );
			
			// initiate hover event
			$( '.cyboslider-caption' ).hover( 
				function() { self.hoverStart( this ); }, 
				function() { self.hoverStop( this ); }
			);
			
			// start slider
			self.timer = setInterval( self.next, duration );
		};
		
		/**
		 * show the next slide
		 */
		self.next = function next() {
			var $active  = $( '.cyboslider-caption.active' ),
			    currentX = parseInt( $active.attr( 'data-cyboslider-item' ) ),
			    nextX    = self.getNextX( currentX );
			
			// show next slide
			self.displayX( nextX );
		};
		
		/**
		 * get next slide number (x)
		 * 
		 * if its not the last slide, x+1 is returned
		 * else the function returns 0
		 * 
		 * @param    int    currentX    the current item number
		 * @return   int                the next item number
		 */
		self.getNextX = function getNextX( currentX ) {
			var nextX = currentX + 1,
			    nextLength = $( '.cyboslider-caption-' + nextX ).length;
			
			return 1 === nextLength ? nextX : 0; 
		};
		
		/**
		 * display slide x
		 * 
		 * @param    int    x    the number of the slide to show
		 */
		self.displayX = function displayX( x ) {
			var hight = $( '.cyboslider-image-1' ).outerHeight();
			
			// swap active caption
			$( '.cyboslider-caption.active' ).removeClass( 'active' );
			$( '.cyboslider-caption-' + x ).addClass( 'active' );
			
			// swap active slide
			$( '#cyboslider-images-list' ).animate( {
				top : hight * x * -1 
			}, 200 );
		};
		
		/**
		 * on mouse over caption swap to the corresponding element and desable the timer
		 * 
		 * @param    object    element    the caption over which the mouse is
		 */
		self.hoverStart = function hoverStart( element ) {
			// stop the timer
			clearInterval( self.timer );
			
			// show the element over wich the mouse is
			var currentX = parseInt( $( element ).attr( 'data-cyboslider-item' ) );
			self.displayX( currentX );
		};
		
		/**
		 * on mouse leave caption restart the timer
		 * 
		 * @param    object    element    the caption over which the mouse is
		 */
		self.hoverStop = function hoverStop( element ) {
			// restart the timer
			self.timer = setInterval( self.next, duration );
		};
	}

	/**
	 * fires after DOM is loaded
	 */
	$( document ).ready(function() {
		Slider.init();
		
	});
	
	/**
	 * fires on resizeing of the window
	 */
	jQuery( window ).resize( function() {
		
	});
	
} )( jQuery );