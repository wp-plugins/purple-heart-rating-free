jQuery( document ).ready( function() {


	/* Show the popover window */
	jQuery( '.wpbph-button-more' ).popover( 'show' );


	/* prevent the buttons to do anything */
	jQuery( '.wpbph-frontend button' ).click( function( event ) {
		event.preventDefault();
	} );


	/* show the animation */
	jQuery( '.wpbph-button-ok, .wpbph-button-bad, .wpbph-heart-big, .wpbph-bad-value i' ).click( function() {
		var thisObj = jQuery( this );
		wpbph_run_up( thisObj.parent().parent().parent().find( '.wpbph-value-inner' ), 0, 71 );
		window.setTimeout( function() {
			wpbph_run_up( thisObj.parent().parent().parent().find( '.wpbph-bad-value-inner' ), 0, 29 );
		}, 300 );
	} );


	function wpbph_run_up( e, from, to ) {
		to = parseInt( to );
		from = parseInt( from );

		e.text( from );

		if( 0 == to ) return;

		from = from + 1;


		if( from > to ) {
			e.css( 'opacity', 0.1 );
			e.animate( { 'opacity':1 }, 1000 );
			return;
		}

		window.setTimeout( function() {
			wpbph_run_up( e, from, to );
		}, 10 );
	}


	function refresh_html( e ) {
		var edit_class = e.data( 'editclass' );
		var input_text = e.val();
		jQuery( '.' + edit_class ).text( input_text );
	}


	/* Make some fields editable */
	jQuery( '.wpbph-editable input, .wpbph-editable textarea' ).keyup( function() {
		refresh_html( jQuery( this ) );
	} );


	/* Make the fields editable */
	add_click_event( jQuery( '.wpbph-headline, .wpbph-description, .wpbph-button-more, .popover-title, .popover-content' ) );


	function add_click_event( e ) {
		e.on( 'click', function( event ) {
			if( jQuery( this ).data( 'hasInput' ) ) return;
			event.preventDefault();
			var html_text = jQuery( this ).text();
			if( jQuery( this ).hasClass( 'wpbph-button-more' ) || jQuery( this ).hasClass( 'popover-title' ) )
				var new_input = jQuery( '<input type="text" class="wpbph-editable-input" value="' + html_text + '" />' );
			else
				var new_input = jQuery( '<textarea class="wpbph-editable-textarea">' + html_text + '</textarea>' );

			add_focusout_event( new_input );
			add_keyup_event( new_input );
			jQuery( this ).html( new_input ).find( 'input, textarea' ).select();
			jQuery( this ).data( 'hasInput', true );
		} );
	}


	function add_keyup_event( e ) {
		e.on( 'keyup', function() {
			/* get the value */
			var input_text = jQuery( this ).val();

			/* where is the parent? */
			var parent_el = jQuery( this ).parent();

			/* get the id */
			if( parent_el.hasClass( 'popover-title' ) ) var input_id = '#wpbph_more_button_headline';
			else if( parent_el.hasClass( 'popover-content' ) ) var input_id = '#wpbph_more_button_description';
			else var input_id = parent_el.data( 'forid' );

			/* set the value for the element */
			jQuery( input_id ).val( input_text );
		} );
	}


	function add_focusout_event( e ) {
		e.on( 'focusout', function() {

			/* get the value */
			var input_text = jQuery( this ).val();

			/* where is the parent? */
			var parent_el = jQuery( this ).parent();

			/* include the text to the parent */
			parent_el.text( input_text );

			/* get the id */
			if( parent_el.hasClass( 'popover-title' ) ) var input_id = '#wpbph_more_button_headline';
			else if( parent_el.hasClass( 'popover-content' ) ) var input_id = '#wpbph_more_button_description';
			else var input_id = parent_el.data( 'forid' );

			/* set the value for the element */
			jQuery( input_id ).val( input_text );

			/* Reset the hasInput flag */
			parent_el.data( 'hasInput', false );

			/* Remove the input field */
			jQuery( this ).remove();
		} );
	}


	/* Reset the values to the defaults */
	jQuery( '.wpbph-reset' ).click( function( e ) {
		e.preventDefault();
		jQuery( '.wpbph-backend-input input, .wpbph-backend-input textarea' ).each( function() {
			jQuery( this ).val( jQuery( this ).data( 'standard-value' ) );
			refresh_html( jQuery( this ) );
			if( jQuery( this ).attr( 'id' ) == 'wpbph_color' ) {
				jQuery( this ).wpColorPicker( 'color', jQuery( this ).data( 'standard-value' ) );
			}
		} );
	} );


	/* Show the edit-icon on the editable places */
	jQuery( '.wpbph-headline, .wpbph-description, .wpbph-button-more, .popover-title, .popover-content' ).hover( function() {
		var offset = jQuery( this ).position();
		var width = jQuery( this ).width();

		var from_left = offset.left + width + 10;
		var from_top = offset.top + 10;

		if( jQuery( this ).hasClass( 'popover-title' ) || jQuery( this ).hasClass( 'popover-content' ) ) {
			var parent_position = jQuery( '.popover' ).position();
			from_left = parent_position.left + jQuery( '.popover' ).width();
			from_top = parent_position.top + 5;
		}

		jQuery( '.wpbph-icon-edit' ).show().css( 'left', ( from_left ) );
		jQuery( '.wpbph-icon-edit' ).css( 'top', from_top );
	}, function() {
		jQuery( '.wpbph-icon-edit' ).hide();
	} );


	/** Subscribe form **/
	jQuery( '.wpbuddy-cr-form a.button' ).click( function( e ) {
		e.preventDefault();

		var name = jQuery( '#text1210658' ).val();
		var mail = jQuery( '#text1210692' ).val();

		jQuery( [
			'<form style="display:none;" action="https://10955.cleverreach.com/f/54067/wcs/" method="post" target="_blank">',
			'<input id="text1210692" name="email" value="' + mail + '" type="text"  />',
			'<input id="text1210658" name="209681" type="text" value="' + name + '"  />',
			'</form>'
		].join( '' ) ).appendTo( 'body' )[0].submit();

	} );


	/************************************************************
	 * Color Pickers
	 ************************************************************/

	if( jQuery.isFunction( jQuery.fn.wpColorPicker ) ) {
		jQuery( 'input.wpbph-color-picker' ).wpColorPicker( {
			'change':function( event, ui ) {

				// create color object
				var color = jQuery.Color( ui.color.toString() );

				jQuery( '.wpbph-table-big-heart, .wpbph-value, h1.wpbph-headline' ).css( 'color', color );

				jQuery( '.wpbph-frontend button.wpbph-button-bad, .wpbph-frontend button.wpbph-button-ok' ).css( 'backgroundColor', color );

				color = color.saturation( .7 );
				color = color.lightness( 0.75 );

				jQuery( '.wpbph-description, .wpbph-button-ok i, .wpbph-copyright-info a' ).css( 'color', color );

				jQuery( '#wpbph_color_light' ).val( color.toHexString() );

			}
		} );
	}

} );


/* ===========================================================
 * bootstrap-tooltip.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#tooltips
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
!function( $ ) {

	"use strict"; // jshint ;_;


	/* TOOLTIP PUBLIC CLASS DEFINITION
	 * =============================== */

	var Tooltip = function( element, options ) {
		this.init( 'tooltip', element, options )
	}

	Tooltip.prototype = {

		constructor     :Tooltip, init:function( type, element, options ) {
			var eventIn
				, eventOut

			this.type = type
			this.$element = $( element )
			this.options = this.getOptions( options )
			this.enabled = true

			if( this.options.trigger == 'click' ) {
				this.$element.on( 'click.' + this.type, this.options.selector, $.proxy( this.toggle, this ) )
			} else if( this.options.trigger != 'manual' ) {
				eventIn = this.options.trigger == 'hover' ? 'mouseenter' : 'focus'
				eventOut = this.options.trigger == 'hover' ? 'mouseleave' : 'blur'
				this.$element.on( eventIn + '.' + this.type, this.options.selector, $.proxy( this.enter, this ) )
				this.$element.on( eventOut + '.' + this.type, this.options.selector, $.proxy( this.leave, this ) )
			}

			this.options.selector ?
				(this._options = $.extend( {}, this.options, { trigger:'manual', selector:'' } )) :
				this.fixTitle()
		}, getOptions   :function( options ) {
			options = $.extend( {}, $.fn[this.type].defaults, options, this.$element.data() )

			if( options.delay && typeof options.delay == 'number' ) {
				options.delay = {
					show:options.delay, hide:options.delay
				}
			}

			return options
		}, enter        :function( e ) {
			var self = $( e.currentTarget )[this.type]( this._options ).data( this.type )

			if( !self.options.delay || !self.options.delay.show ) return self.show()

			clearTimeout( this.timeout )
			self.hoverState = 'in'
			this.timeout = setTimeout( function() {
				if( self.hoverState == 'in' ) self.show()
			}, self.options.delay.show )
		}, leave        :function( e ) {
			var self = $( e.currentTarget )[this.type]( this._options ).data( this.type )

			if( this.timeout ) clearTimeout( this.timeout )
			if( !self.options.delay || !self.options.delay.hide ) return self.hide()

			self.hoverState = 'out'
			this.timeout = setTimeout( function() {
				if( self.hoverState == 'out' ) self.hide()
			}, self.options.delay.hide )
		}, show         :function() {
			var $tip
				, inside
				, pos
				, actualWidth
				, actualHeight
				, placement
				, tp

			if( this.hasContent() && this.enabled ) {
				$tip = this.tip()
				this.setContent()

				if( this.options.animation ) {
					$tip.addClass( 'fade' )
				}

				placement = typeof this.options.placement == 'function' ?
					this.options.placement.call( this, $tip[0], this.$element[0] ) :
					this.options.placement

				inside = /in/.test( placement )

				$tip
					.detach()
					.css( { top:0, left:0, display:'block' } )
					.insertAfter( this.$element )

				pos = this.getPosition( inside )

				actualWidth = $tip[0].offsetWidth
				actualHeight = $tip[0].offsetHeight

				switch( inside ? placement.split( ' ' )[1] : placement ) {
					case 'bottom':
						tp = {top:pos.top + pos.height, left:pos.left + pos.width / 2 - actualWidth / 2}
						break
					case 'top':
						tp = {top:pos.top - actualHeight, left:pos.left + pos.width / 2 - actualWidth / 2}
						break
					case 'left':
						tp = {top:pos.top + pos.height / 2 - actualHeight / 2, left:pos.left - actualWidth}
						break
					case 'right':
						tp = {top:pos.top + pos.height / 2 - actualHeight / 2, left:pos.left + pos.width}
						break
				}

				$tip
					.offset( tp )
					.addClass( placement )
					.addClass( 'in' )
			}
		}, setContent   :function() {
			var $tip = this.tip()
				, title = this.getTitle()

			$tip.find( '.tooltip-inner' )[this.options.html ? 'html' : 'text']( title )
			$tip.removeClass( 'fade in top bottom left right' )
		}, hide         :function() {
			var that = this
				, $tip = this.tip()

			$tip.removeClass( 'in' )

			function removeWithAnimation() {
				var timeout = setTimeout( function() {
					$tip.off( $.support.transition.end ).detach()
				}, 500 )

				$tip.one( $.support.transition.end, function() {
					clearTimeout( timeout )
					$tip.detach()
				} )
			}

			$.support.transition && this.$tip.hasClass( 'fade' ) ?
				removeWithAnimation() :
				$tip.detach()

			return this
		}, fixTitle     :function() {
			var $e = this.$element
			if( $e.attr( 'title' ) || typeof($e.attr( 'data-original-title' )) != 'string' ) {
				$e.attr( 'data-original-title', $e.attr( 'title' ) || '' ).removeAttr( 'title' )
			}
		}, hasContent   :function() {
			return this.getTitle()
		}, getPosition  :function( inside ) {
			return $.extend( {}, (inside ? {top:0, left:0} : this.$element.offset()), {
				width:this.$element[0].offsetWidth, height:this.$element[0].offsetHeight
			} )
		}, getTitle     :function() {
			var title
				, $e = this.$element
				, o = this.options

			title = $e.attr( 'data-original-title' )
				|| (typeof o.title == 'function' ? o.title.call( $e[0] ) : o.title)

			return title
		}, tip          :function() {
			return this.$tip = this.$tip || $( this.options.template )
		}, validate     :function() {
			if( !this.$element[0].parentNode ) {
				this.hide()
				this.$element = null
				this.options = null
			}
		}, enable       :function() {
			this.enabled = true
		}, disable      :function() {
			this.enabled = false
		}, toggleEnabled:function() {
			this.enabled = !this.enabled
		}, toggle       :function( e ) {
			var self = $( e.currentTarget )[this.type]( this._options ).data( this.type )
			self[self.tip().hasClass( 'in' ) ? 'hide' : 'show']()
		}, destroy      :function() {
			this.hide().$element.off( '.' + this.type ).removeData( this.type )
		}

	}


	/* TOOLTIP PLUGIN DEFINITION
	 * ========================= */

	var old = $.fn.tooltip

	$.fn.tooltip = function( option ) {
		return this.each( function() {
			var $this = $( this )
				, data = $this.data( 'tooltip' )
				, options = typeof option == 'object' && option
			if( !data ) $this.data( 'tooltip', (data = new Tooltip( this, options )) )
			if( typeof option == 'string' ) data[option]()
		} )
	}

	$.fn.tooltip.Constructor = Tooltip

	$.fn.tooltip.defaults = {
		animation:true, placement:'top', selector:false, template:'<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>', trigger:'hover', title:'', delay:0, html:false
	}


	/* TOOLTIP NO CONFLICT
	 * =================== */

	$.fn.tooltip.noConflict = function() {
		$.fn.tooltip = old
		return this
	}

}( window.jQuery );
/* ===========================================================
 * bootstrap-popover.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#popovers
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================== */


!function( $ ) {

	"use strict"; // jshint ;_;


	/* POPOVER PUBLIC CLASS DEFINITION
	 * =============================== */

	var Popover = function( element, options ) {
		this.init( 'popover', element, options )
	}


	/* NOTE: POPOVER EXTENDS BOOTSTRAP-TOOLTIP.js
	 ========================================== */

	Popover.prototype = $.extend( {}, $.fn.tooltip.Constructor.prototype, {

		constructor  :Popover, setContent:function() {
			var $tip = this.tip()
				, title = this.getTitle()
				, content = this.getContent()

			$tip.find( '.popover-title' )[this.options.html ? 'html' : 'text']( title )
			$tip.find( '.popover-content' )[this.options.html ? 'html' : 'text']( content )

			$tip.removeClass( 'fade top bottom left right in' )
		}, hasContent:function() {
			return this.getTitle() || this.getContent()
		}, getContent:function() {
			var content
				, $e = this.$element
				, o = this.options

			content = $e.attr( 'data-content' )
				|| (typeof o.content == 'function' ? o.content.call( $e[0] ) : o.content)

			return content
		}, tip       :function() {
			if( !this.$tip ) {
				this.$tip = $( this.options.template )
			}
			return this.$tip
		}, destroy   :function() {
			this.hide().$element.off( '.' + this.type ).removeData( this.type )
		}

	} )


	/* POPOVER PLUGIN DEFINITION
	 * ======================= */

	var old = $.fn.popover

	$.fn.popover = function( option ) {
		return this.each( function() {
			var $this = $( this )
				, data = $this.data( 'popover' )
				, options = typeof option == 'object' && option
			if( !data ) $this.data( 'popover', (data = new Popover( this, options )) )
			if( typeof option == 'string' ) data[option]()
		} )
	}

	$.fn.popover.Constructor = Popover

	$.fn.popover.defaults = $.extend( {}, $.fn.tooltip.defaults, {
		placement:'right', trigger:'click', content:'', template:'<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>'
	} )


	/* POPOVER NO CONFLICT
	 * =================== */

	$.fn.popover.noConflict = function() {
		$.fn.popover = old
		return this
	}

}( window.jQuery );