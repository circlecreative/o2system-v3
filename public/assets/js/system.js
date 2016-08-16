/**
 * o2system
 *
 * An open source application development framework for PHP 5.4 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS ||
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS || COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES || OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT || OTHERWISE, ARISING FROM,
 * OUT OF || IN CONNECTION WITH THE SOFTWARE || THE USE || OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        o2system
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
 * @license        http://circle-creative.com/products/o2system/license.html
 * @license        http://opensource.org/licenses/MIT    MIT License
 * @link           http://o2system.center
 * @since          Version 3.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * O2System
 *
 * @type {Object}
 */
var o2system = {};

(function ( $ ) {
	o2system = $.o2system = {};

	$.o2system.version = '3.0.0';

	/**
	 * O2System Active Registry
	 *
	 * @type {Object}
	 */
	$.o2system.active = {};

// ----------------------------------------- Active -- END -->

	/**
	 * SERVER Object
	 *
	 * @type {Object}
	 */
	$.o2system.Server = {};

	/**
	 * SERVER Query String
	 *
	 * @type {string}
	 */
	$.o2system.Server.QUERY_STRING = window.location.search.substring( 1 );

	/**
	 * SERVER GET Params
	 *
	 * @type {Array}
	 */
	$.o2system.Server.GET = [];

	$.o2system.Server.QUERY_PARAMS = $.o2system.Server.QUERY_STRING.split( '&' );

	if ( $.o2system.Server.QUERY_PARAMS.length > 0 ) {
		for ( var i = 0; i < $.o2system.Server.QUERY_PARAMS.length; i ++ ) {
			var PARAM = $.o2system.Server.QUERY_PARAMS[ i ].split( '=' );
			$.o2system.Server.GET[ PARAM[ 0 ] ] = PARAM[ 1 ];

			// UNSET PARAM
			delete PARAM;
		}
	}

	$.o2system.Server.HOSTNAME = window.location.host;
	var x_hostname = $.o2system.Server.HOSTNAME.split( '.' );

	if ( x_hostname.length == 3 ) {
		$.o2system.active.domain = x_hostname[ 1 ] + '.' + x_hostname[ 2 ];

		if ( x_hostname[ 0 ] != 'www' ) {
			$.o2system.active.subdomain = x_hostname[ 0 ];
		}

	}
	// else if (x_hostname[ 0 ] == 192 && x_hostname[ 1 ] == 168 && x_hostname[ 2 ] == 1) {
	// 	$.o2system.active.domain = x_hostname[ 0 ] + '.' + x_hostname[ 1 ] + '.' + x_hostname[ 2 ] + '.' + x_hostname[ 3 ];
	// }
	else {
		$.o2system.active.domain = x_hostname[ 0 ] + '.' + x_hostname[ 1 ];
	}

// UNSET x_hostname
	delete x_hostname;

	$.o2system.Server.HASH = window.location.hash;

	/**
	 * Browser Object
	 *
	 * @type {Object}
	 */
	$.o2system.Browser = {};

	/**
	 * Browser Open Window
	 *
	 * @param url
	 * @param title
	 * @param width
	 * @param height
	 */
	$.o2system.Browser.open = function ( url , title , width , height ) {
		// Fixes dual-screen position Most browsers Firefox
		var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
		var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

		var screenWidth = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
		var screenHeight = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

		var left = ((screenWidth / 2) - (width / 2)) + dualScreenLeft;
		var top = ((screenHeight / 2) - (height / 2)) + dualScreenTop;
		var newWindow = window.open( url , title , 'toolbar=no, menubar=no, resizable=no, copyhistory=no, location=no, directories=no, status=no, addressbar=0, scrollbars=no, width=' + width + ', height=' + height + ', top=' + top + ', left=' + left );

		// Puts focus on the newWindow
		if ( window.focus ) {
			newWindow.focus();
		}
	};

	/**
	 * Browser Redirect
	 *
	 * @param   uri
	 *
	 * @return  bool
	 */
	$.o2system.Browser.redirect = function ( uri ) {

		if ( typeof uri !== 'undefined' ) {
			if ( uri.indexOf( 'http' ) ) {
				return window.location.href = uri;
			}
		}

		return window.location.href = $.o2system.URL.Base( uri );
	};

// ----------------------------------------- Browser -- END -->

	/**
	 * URI Object
	 *
	 * @type {Object}
	 */
	$.o2system.URI = {};

	/**
	 * URI String
	 *
	 * @type {string}
	 */
	$.o2system.URI.string = window.location.pathname;

	/**
	 * URI String
	 *
	 * @type {Array}
	 */
	$.o2system.URI.segments = $.o2system.URI.string.split( '/' );
	$.o2system.URI.segments.shift();

	/**
	 * URI Segment
	 *
	 * @param   n
	 *
	 * @return  string
	 */
	$.o2system.URI.segment = function ( n ) {
		if ( $.o2system.URI.segments.hasOwnProperty( n - 1 ) ) {
			return $.o2system.URI.segments[ n - 1 ];
		}

		return null;
	};

	/**
	 * URI Segment
	 *
	 * @param   segment
	 *
	 * @return  bool
	 */
	$.o2system.URI.hasSegment = function ( segment ) {
		if ( $.o2system.URI.segments.indexOf( segment ) ) {
			return true;
		}

		return false;
	};

// ----------------------------------------- URI -- END -->

	/**
	 * URL Object
	 *
	 * @type {Object}
	 */
	$.o2system.URL = {};

	/**
	 * URL Base
	 *
	 * @type {string}
	 */
	$.o2system.URL.Base = function ( uri ) {
		uri = typeof uri === 'undefined' ? '' : uri;
		uri = uri instanceof Array ? uri.join() : uri;

		return window.location.protocol + '//' + window.location.hostname + '/' + uri;
	};

	/**
	 * URL Current
	 *
	 * @type {string}
	 */
	$.o2system.URL.Current = function ( uri ) {
		uri = typeof uri === 'undefined' ? '' : uri;
		uri = uri instanceof Array ? uri.join() : uri;

		var current = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
		current = current.replace('.html', '');

		return uri == '' ? current : current + '/' + uri;
	};

// ----------------------------------------- URL -- END -->

	/**
	 * Input Object
	 *
	 * @type {Object}
	 */
	$.o2system.Input = {};

	/**
	 * Input Get
	 * @param   index
	 * @return  string
	 */
	$.o2system.Input.get = function ( index ) {
		if ( $.o2system.Server.GET.hasOwnProperty( index ) ) {
			return $.o2system.Server.GET[ index ];
		}

		return null;
	};

	/**
	 * Input Build HTTP Query Method
	 *
	 * @param   queryObject
	 * @return  string
	 */
	$.o2system.Input.buildQuery = function ( queryObject ) {
		if ( typeof queryObject == 'undefined' ) {
			queryObject = $.o2system.Server.GET;
		}

		var build_query = '?';
		$.o2system.each( queryObject , function ( key , value ) {
			if ( value !== 'undefined' && value !== '' && key !== '' ) {
				build_query = build_query + key + '=' + value + '&';
			}
		} );

		return build_query.substring( 0 , (build_query.length - 1) );
	};

	/**
	 * Input Validate URL
	 *
	 * @param   string
	 * @return  bool
	 */
	$.o2system.Input.isValidURL = function ( string ) {
		var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
		return regexp.test( string );
	};

	/**
	 * Input Validate Domain
	 *
	 * @param   string
	 * @return  bool
	 */
	$.o2system.Input.isValidDomain = function ( string ) {
		var regexp = /^((?:(?:(?:\w[\.\-\+]?)*)\w)+)((?:(?:(?:\w[\.\-\+]?){0,62})\w)+)\.(\w{2,6})$/;
		return regexp.test( string );
	};

	/**
	 * Input Validate AlphaNumeric
	 *
	 * @param   string
	 * @return  bool
	 */
	$.o2system.Input.isAlphaNumeric = function ( string ) {
		var regexp = /^([a-zA-Z0-9-]+)$/;
		return regexp.test( string );
	};

	/**
	 * Input Validate Empty
	 *
	 * @param   string
	 * @return  bool
	 */
	$.o2system.Input.isEmpty = function ( string ) {
		if ( string.trim() ) {
			return true;
		}
		return false;
	};

	/**
	 * Input Validate Function
	 *
	 * @param mixed
	 * @returns {boolean}
	 */
	$.o2system.Input.isFunction = function ( functionVariable ) {
		var getType = {};

		if ( functionVariable && getType.toString.call( functionVariable ) === '[object Function]' ) {
			return true;
		}

		return false;
	};

	/**
	 * Input Sanitize String
	 *
	 * @param   string
	 * @return  string
	 */
	$.o2system.Input.sanitize = function ( string ) {
		var tagBody = '(?:[^"\'>]|"[^"]*"|\'[^\']*\')*';

		var tagOrComment = new RegExp(
			'<(?:'
			// Comment body.
			+ '!--(?:(?:-*[^->])*--+|-?)'
			// Special "raw text" elements whose content should be elided.
			+ '|script\\b' + tagBody + '>[\\s\\S]*?</script\\s*'
			+ '|style\\b' + tagBody + '>[\\s\\S]*?</style\\s*'
			// Regular name
			+ '|/?[a-z]'
			+ tagBody
			+ ')>' ,
			'gi' );

		var oldString;
		do {
			oldString = string;
			string = string.replace( tagOrComment , '' );
		} while ( string !== oldString );
		return string.replace( /</g , '&lt;' );
	};

	/**
	 * Get Input Hash
	 *
	 * @return  string
	 */
	$.o2system.Input.hash = function () {
		return $.o2system.Server.HASH;
	};

	/**
	 * Input Set Cookie
	 *
	 * @param   name
	 * @param   value
	 * @param   expires
	 * @param   path
	 * @param   domain
	 * @param   secure
	 * @param   httponly
	 * @return  string
	 */
	$.o2system.Input.setCookie = function ( name , value , expires , path , domain , secure , httponly ) {
		name = typeof name !== 'undefined' ? name : '';
		value = typeof value !== 'undefined' ? value : '';
		expires = typeof expires !== 'undefined' ? expires : (24 * 60 * 60 * 1000);
		path = typeof path !== 'undefined' ? path : '';
		domain = typeof domain !== 'undefined' ? domain : '';
		secure = typeof secure !== 'undefined' ? secure : false;
		httponly = typeof httponly !== 'undefined' ? httponly : false;

		var date = new Date();
		date.setTime( date.getTime() + expires );
		expires = date.toUTCString();
		return document.cookie = [ name , '=' , value , expires ? '; expires=' + expires : '' , path ? '; path=' + path : '' , domain ? '; domain=' + domain : '' , secure ? '; secure' : '' , httponly ? '; httponly' : '' ].join( '' );
	};

	/**
	 * Cookie Get Method
	 *
	 * @param   name
	 * @return  string
	 */
	$.o2system.Input.cookie = function ( name ) {
		var nameCookie = name + "=";
		var docCookie = document.cookie.split( ';' );
		for ( var i = 0; i < docCookie.length; i ++ ) {
			var cookie = docCookie[ i ];
			while ( cookie.charAt( 0 ) == ' ' ) {
				cookie = cookie.substring( 1 );
			}
			if ( cookie.indexOf( nameCookie ) == 0 ) {
				return cookie.substring( nameCookie.length , cookie.length );
			}
		}
		return '';
	};

	/**
	 * Cookie Exists Method
	 *
	 * @param   name
	 * @return  bool
	 */
	$.o2system.Input.isCookieExists = function ( name ) {
		var cookie = $.o2system.Cookie.get( name );

		if ( cookie == '' ) {
			return false;
		}

		return true;
	};

	/**
	 * Delete Cookie Method
	 *
	 * @param name
	 * @param path
	 * @param domain
	 */
	$.o2system.Input.deleteCookie = function ( name , path , domain ) {
		if ( $.o2system.Input.isCookieExists( name ) ) {
			$.o2system.Input.setCookie( name , '' , - 1 , path , domain );
		}
	};

// ----------------------------------------- Input -- END -->

	/**
	 * Helper Object
	 *
	 * @type {Object}
	 */
	$.o2system.Helper = {};

	/**
	 * Helper Number Format
	 *
	 * @param   number
	 *
	 * @return  string
	 */
	$.o2system.Helper.numberFormat = function ( number ) {
		return ("" + number).replace( /(\d)(?=(\d\d\d)+(?!\d))/g , function ( $1 ) {
			return $1 + "."
		} );
	};

	/**
	 * Helper Function Execute
	 *
	 * @param method
	 * @param arguments
	 *
	 * @return mixed
	 */
	$.o2system.Helper.callFunction = function ( method , args ) {
		if ( $.o2system.Input.isFunction( method ) ) {
			return method( args );
		} else {
			var context = window;
			var namespaces = method.split( '.' );
			var func = namespaces.pop();
			for ( var i = 0; i < namespaces.length; i ++ ) {
				context = context[ namespaces[ i ] ];
			}
			return context[ func ].call( context , args );
		}
	};

// ----------------------------------------- Helper -- END -->

	/**
	 * Cache Object
	 *
	 * @type {Object}
	 */
	$.o2system.Cache = {};

	/**
	 * Cache Save
	 *
	 * @param key
	 * @param value
	 *
	 * return bool
	 */
	$.o2system.Cache.save = function ( key , value ) {
		return localStorage.setItem( key , value );
	};

	/**
	 * Cache Get
	 *
	 * @param key
	 *
	 * @return string
	 */
	$.o2system.Cache.get = function ( key ) {
		return localStorage.getItem( key );
	};

	/**
	 * Cache Delete
	 *
	 * @param key
	 *
	 * @return bool
	 */
	$.o2system.Cache.delete = function ( key ) {
		return localStorage.removeItem( key );
	};

// ----------------------------------------- Cache -- END -->

	/**
	 * Session Object
	 *
	 * @type {Object}
	 */
	$.o2system.Session = {};

	/**
	 * Session Set Userdata
	 *
	 * @param key
	 * @param value
	 *
	 * @return bool
	 */
	$.o2system.Session.setUserdata = function ( key , value ) {
		return sessionStorage.setItem( key , value );
	};

	/**
	 * Session Get Userdata
	 *
	 * @param key
	 *
	 * @return string
	 */
	$.o2system.Session.getUserdata = function ( key ) {
		return sessionStorage.getItem( key );
	};

	/**
	 * Session Has Userdata
	 *
	 * @param   key
	 * @return  bool
	 */
	$.o2system.Session.hasUserdata = function ( key ) {
		if ( sessionStorage.getItem( key ) ) {
			return true;
		}
		return false;
	};

	/**
	 * Session Unset Userdata
	 *
	 * @param   key
	 * @return  bool
	 */
	$.o2system.Session.unsetUserdata = function ( key ) {
		return sessionStorage.removeItem( key );
	};

// ----------------------------------------- Session -- END -->

	/**
	 * Security Object
	 *
	 * @type {Object}
	 */
	$.o2system.Security = {};

	/**
	 * Get CSRF Token
	 *
	 * @return string
	 */
	$.o2system.Security.getCSRFToken = function () {
		return $.o2system.Input.cookie( 'csrf' );
	};

// ----------------------------------------- Security -- END -->

	/**
	 * View Object
	 *
	 * @type {HTMLElement}
	 */
	$.o2system.View = document.body;

// ----------------------------------------- View -- END -->

	/**
	 * Websocket Object
	 *
	 * @type {{}}
	 */
	$.o2system.Websocket = {};

	/**
	 * Websocket Handler
	 *
	 * @type Websocket|null
	 */
	$.o2system.Websocket.handler = null;

	/**
	 * Websocket Verbose Mode
	 *
	 * @type    bool
	 */
	$.o2system.Websocket.verbose = false;

	/**
	 * Open Websocket
	 *
	 * @param hostname
	 * @param port
	 */
	$.o2system.Websocket.open = function ( hostname , port ) {
		hostname = typeof hostname === 'undefined' ? $.o2system.active.domain : hostname;
		port = typeof port === 'undefined' ? 8000 : port;

		$.o2system.Websocket.handler = new WebSocket( 'ws://' + hostname + ':' + port );

		try {
			console.log( 'Connecting... (readyState ' + $.o2system.Websocket.handler.readyState + ')' );
			$.o2system.Websocket.handler.onopen = function ( response ) {
				if ( $.o2system.Websocket.verbose ) {
					console.log( 'Connection successfully opened (readyState ' + this.readyState + ')' );
				}
			};

			$.o2system.Websocket.handler.onmessage = function ( response ) {
				if ( $.o2system.Websocket.verbose ) {
					console.log( response );
				}
			};

			$.o2system.Websocket.handler.onclose = function ( response ) {
				if ( $.o2system.Websocket.verbose ) {
					if ( this.readyState == 2 )
						console.log( 'Closing... The connection is going throught the closing handshake (readyState ' + this.readyState + ')' );
					else if ( this.readyState == 3 )
						console.log( 'Connection closed... The connection has been closed or could not be opened (readyState ' + this.readyState + ')' );
					else
						console.log( 'Connection closed... (unhandled readyState ' + this.readyState + ')' );
				}
			};

			$.o2system.Websocket.handler.onerror = function ( response ) {
				console.error( 'Connection error... (' + response.data + ')' );
			};
		}
		catch ( exception ) {
			console.error( exception );
		}
	};

	/**
	 * Send Websocket request
	 *
	 * @param request
	 */
	$.o2system.Websocket.send = function ( request ) {
		$.o2system.Websocket.handler.send( request );
	};

	/**
	 * Close Websocket
	 */
	$.o2system.Websocket.close = function () {
		$.o2system.Websocket.handler.close();
		$.o2system.Websocket.handler = null;
	};

}( jQuery ));