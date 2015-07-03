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
			// resize the slider for mobile devices
			self.setDims();
			
			// set the first one as active
			$( '.cyboslider-caption-0, .cyboslider-mobile-button-0' ).addClass( 'active' );
			
			// initiate hover event
			$( '.cyboslider-caption, .cyboslider-mobile-button' ).hover( 
				function() { self.hoverStart( this ); }, 
				function() { self.hoverStop( this ); }
			);
			
			// bind mobile button click event
			$( '.cyboslider-mobile-button' ).click( function() {
				self.hoverStart( this );
			});
			
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
			$( '.cyboslider-caption.active, .cyboslider-mobile-button.active' ).removeClass( 'active' );
			$( '.cyboslider-caption-' + x + ', .cyboslider-mobile-button-' + x ).addClass( 'active' );
			
			// swap active slide
			$( '#cyboslider-images-list' ).stop( true ).animate( { // the ".stop( true )" clears the animation queue 
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
		
		/**
		 * makes the slider responsive
		 * 
		 * sets the dimensions of the slider elements regarding to the screensize
		 */
		self.setDims = function setDims() {
			var c               = new Object(),
			    windowWidth     = $( window ).width(),
			    $wrapper        = $( '#cyboslider-wrapper' ),
			    $screen         = $( '#cyboslider-screen' ),
			    $buttons        = $( '#cyboslider-mobile-buttons-list' ),
			    $imageLi        = $( '.cyboslider-image' ),
			    wrapperPaddingH = parseInt( $wrapper.css( 'padding-left' ) ) +
			                      parseInt( $wrapper.css( 'padding-right' ) ),
			    wrapperPaddingV = parseInt( $wrapper.css( 'padding-top' ) ) +
			                      parseInt( $wrapper.css( 'padding-bottom' ) );
			
			// parseInt from cybosliderConst values (they come from the php)
			$.each( cybosliderConst, function( key, value ) {
			  c[ key ] = parseInt( value );
			} );
			
			switch ( true ) {
				// desktop
				case ( windowWidth >= c.width ) :
					$wrapper.width( parseInt( c.width ) - wrapperPaddingH )
					        .height( c.height - wrapperPaddingV );
					$screen.width( c.imageWidth )
					       .height( c.imageHeight );
					$imageLi.width( c.imageWidth )
					        .height( c.imageHeight );
					break;
				
				// intermediate (smaller size but with whitespace on the side)
				case ( windowWidth < c.width && windowWidth >= c.intermediateWidth ) :
					$wrapper.width( c.intermediateWidth - wrapperPaddingH )
					        .height( c.height + c.captionsHeight - wrapperPaddingV );
					$screen.width( c.imageWidth )
					       .height( c.imageHeight + c.captionsHeight );
					$imageLi.width( c.imageWidth )
					        .height( c.imageHeight + c.captionsHeight );
					$buttons.width( c.intermediateWidth - wrapperPaddingH - c.imageWidth );
					break;
				
				// intermediate (without whitespace but image not scaled yet)
				case ( windowWidth < c.intermediateWidth && windowWidth >= c.imageWidth + wrapperPaddingV ) :
					$wrapper.width( windowWidth - wrapperPaddingH )
					        .height( c.height + c.captionsHeight - wrapperPaddingV );
					$screen.width( c.imageWidth )
					       .height( c.imageHeight + c.captionsHeight );
					$imageLi.width( c.imageWidth )
					        .height( c.imageHeight + c.captionsHeight );
					$buttons.width( windowWidth - wrapperPaddingH - c.imageWidth );
					break;
				
				// mobile
				default:
					$wrapper.width( windowWidth - wrapperPaddingH );
					$wrapper.height( $wrapper.width() * c.imageHeight / c.imageWidth + c.captionsHeight );
					$screen.width( '100%' );
					$screen.height( $screen.width() * c.imageHeight / c.imageWidth + c.captionsHeight );
					$imageLi.width( '100%' )
					        .height( $screen.width() * c.imageHeight / c.imageWidth + c.captionsHeight );
					break;
			}
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
		Slider.setDims();
	});
	
} )( jQuery );