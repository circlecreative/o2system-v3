<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package		O2System
 * @author		Circle Creative Dev Team
 * @copyright	Copyright (c) 2005 - 2015, .
 * @license		http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license	    http://opensource.org/licenses/MIT	MIT License
 * @link		http://circle-creative.com/products/o2system-codeigniter.html
 * @since		Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

/**
 * Text Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/text.html
 */
// ------------------------------------------------------------------------

if( ! function_exists( 'split_text' ) )
{
    /**
     * Split text into array without lossing any word
     *
     * @params string $text
     *
     * @param   string  $text       Text Source
     * @param   string  $splitter   Split Text Marker
     * @param   int     $limit      Split after num of limit characters
     *
     * @return string
     */
    function split_text( $text, $splitter = '<---break--->', $limit = 100 )
    {
        $wrap_text = wordwrap( $text, $limit, $splitter );

        return preg_split( '[' . $splitter . ']', $wrap_text, -1, PREG_SPLIT_NO_EMPTY );
    }
}
// ------------------------------------------------------------------------

if( ! function_exists( 'columnize_text' ) )
{
    /**
     * Split a block of strings or text evenly across a number of columns
     *
     * @param  string $text Text Source
     * @param  int    $cols Number of columns
     *
     * @return array
     */
    function columnize_text( $text, $cols )
    {
        $col_length = ceil( strlen( $text ) / $cols ) + 3;
        $return = explode( "\n", wordwrap( strrev( $text ), $col_length ) );

        if( count( $return ) > $cols )
        {
            $return[ $cols - 1 ] .= " " . $return[ $cols ];
            unset( $return[ $cols ] );
        }

        $return = array_map( "strrev", $return );

        return array_reverse( $return );
    }
}
// ------------------------------------------------------------------------

if( ! function_exists( 'wrap_text' ) )
{
    /**
     * Wrap the given string to a certain chars length and lines
     *
     *
     * @param  string   $text   Text Source
     * @param  int      $chars  10 means wrap at 40 chars
     * @param  int|bool $lines  True or false $lines = false or $lines = 3, means truncate after 3 lines
     *
     * @return string
     */
    function wrap_text( $text, $chars = 10, $lines = FALSE )
    {
        # the simple case - return wrapped words
        if( ! $lines )
        {
            return wordwrap( $text, $chars, "\n" );
        }
        # truncate to maximum possible number of characters
        $return = substr( $text, 0, $chars * $lines );
        # apply wrapping and return first $lines lines
        $return = wordwrap( $return, $chars, "\n" );
        preg_match( "/(.+\n?){0,$lines}/", $return, $regs );

        return $regs[ 0 ];
    }
}
// ------------------------------------------------------------------------

if( ! function_exists( 'trim_text' ) )
{
    /**
     * Cuts the given string to a certain length without breaking a word.
     *
     * @param  string   $text   Text source
     * @param  int      $limit  number of maximum characters leave remaining
     * @param  string   $break  = "." break on dot ending or keep maximum set on ' '
     * @param  string   $ending = '...' to display '...' on the end of the trimmed string
     *
     * @return string
     */
    function trim_text( $text, $limit, $break = '.', $ending = '.' )
    {
        // return with no change if string is shorter than $limit
        if( strlen( $text ) <= $limit )
        {
            return $text;
        }
        // is $break present between $limit and the end of the string?
        if( FALSE !== ( $breakpoint = strpos( $text, $break, $limit ) ) )
        {
            if( $breakpoint < strlen( $text ) - 1 )
            {
                $text = substr( $text, 0, $breakpoint ) . $ending;
            }
        }

        return $text;
    }
}

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
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
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package      CodeIgniter
 * @author       EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license      http://opensource.org/licenses/MIT MIT License
 * @link         http://codeigniter.com
 * @since        Version 1.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Text Helpers
 *
 * @package        CodeIgniter
 * @subpackage     Helpers
 * @category       Helpers
 * @author         EllisLab Dev Team
 * @link           http://codeigniter.com/user_guide/helpers/text_helper.html
 */

// ------------------------------------------------------------------------

if( ! function_exists( 'word_limiter' ) )
{
    /**
     * Word Limiter
     *
     * Limits a string to X number of words.
     *
     * @param    string
     * @param    int
     * @param    string    the end character. Usually an ellipsis
     *
     * @return    string
     */
    function word_limiter( $str, $limit = 100, $end_char = '&#8230;' )
    {
        if( trim( $str ) === '' )
        {
            return $str;
        }

        preg_match( '/^\s*+(?:\S++\s*+){1,' . (int)$limit . '}/', $str, $matches );

        if( strlen( $str ) === strlen( $matches[ 0 ] ) )
        {
            $end_char = '';
        }

        return rtrim( $matches[ 0 ] ) . $end_char;
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'character_limiter' ) )
{
    /**
     * Character Limiter
     *
     * Limits the string based on the character count.  Preserves complete words
     * so the character count may not be exactly as specified.
     *
     * @param    string
     * @param    int
     * @param    string    the end character. Usually an ellipsis
     *
     * @return    string
     */
    function character_limiter( $str, $n = 500, $end_char = '&#8230;' )
    {
        if( mbStrlen( $str ) < $n )
        {
            return $str;
        }

        // a bit complicated, but faster than preg_replace with \s+
        $str = preg_replace( '/ {2,}/', ' ', str_replace( array( "\r", "\n", "\t", "\x0B", "\x0C" ), ' ', $str ) );

        if( mbStrlen( $str ) <= $n )
        {
            return $str;
        }

        $out = '';
        foreach( explode( ' ', trim( $str ) ) as $val )
        {
            $out .= $val . ' ';

            if( mbStrlen( $out ) >= $n )
            {
                $out = trim( $out );

                return ( mbStrlen( $out ) === mbStrlen( $str ) ) ? $out : $out . $end_char;
            }
        }
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'ascii_to_entities' ) )
{
    /**
     * High ASCII to Entities
     *
     * Converts high ASCII text and MS Word special characters to character entities
     *
     * @param    string $str
     *
     * @return    string
     */
    function ascii_to_entities( $str )
    {
        $out = '';
        for( $i = 0, $s = strlen( $str ) - 1, $count = 1, $temp = array(); $i <= $s; $i++ )
        {
            $ordinal = ord( $str[ $i ] );

            if( $ordinal < 128 )
            {
                /*
                    If the $temp array has a value but we have moved on, then it seems only
                    fair that we output that entity and restart $temp before continuing. -Paul
                */
                if( count( $temp ) === 1 )
                {
                    $out .= '&#' . array_shift( $temp ) . ';';
                    $count = 1;
                }

                $out .= $str[ $i ];
            }
            else
            {
                if( count( $temp ) === 0 )
                {
                    $count = ( $ordinal < 224 ) ? 2 : 3;
                }

                $temp[ ] = $ordinal;

                if( count( $temp ) === $count )
                {
                    $number = ( $count === 3 )
                        ? ( ( $temp[ 0 ] % 16 ) * 4096 ) + ( ( $temp[ 1 ] % 64 ) * 64 ) + ( $temp[ 2 ] % 64 )
                        : ( ( $temp[ 0 ] % 32 ) * 64 ) + ( $temp[ 1 ] % 64 );

                    $out .= '&#' . $number . ';';
                    $count = 1;
                    $temp = array();
                }
                // If this is the last iteration, just output whatever we have
                elseif( $i === $s )
                {
                    $out .= '&#' . implode( ';', $temp ) . ';';
                }
            }
        }

        return $out;
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'entities_to_ascii' ) )
{
    /**
     * Entities to ASCII
     *
     * Converts character entities back to ASCII
     *
     * @param    string
     * @param    bool
     *
     * @return    string
     */
    function entities_to_ascii( $str, $all = TRUE )
    {
        if( preg_match_all( '/\&#(\d+)\;/', $str, $matches ) )
        {
            for( $i = 0, $s = count( $matches[ 0 ] ); $i < $s; $i++ )
            {
                $digits = $matches[ 1 ][ $i ];
                $out = '';

                if( $digits < 128 )
                {
                    $out .= chr( $digits );

                }
                elseif( $digits < 2048 )
                {
                    $out .= chr( 192 + ( ( $digits - ( $digits % 64 ) ) / 64 ) ) . chr( 128 + ( $digits % 64 ) );
                }
                else
                {
                    $out .= chr( 224 + ( ( $digits - ( $digits % 4096 ) ) / 4096 ) )
                        . chr( 128 + ( ( ( $digits % 4096 ) - ( $digits % 64 ) ) / 64 ) )
                        . chr( 128 + ( $digits % 64 ) );
                }

                $str = str_replace( $matches[ 0 ][ $i ], $out, $str );
            }
        }

        if( $all )
        {
            return str_replace(
                array( '&amp;', '&lt;', '&gt;', '&quot;', '&apos;', '&#45;' ),
                array( '&', '<', '>', '"', "'", '-' ),
                $str
            );
        }

        return $str;
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'word_censor' ) )
{
    /**
     * Word Censoring Function
     *
     * Supply a string and an array of disallowed words and any
     * matched words will be converted to #### or to the replacement
     * word you've submitted.
     *
     * @param    string    the text string
     * @param    string    the array of censoered words
     * @param    string    the optional replacement value
     *
     * @return    string
     */
    function word_censor( $str, $censored, $replacement = '' )
    {
        if( ! is_array( $censored ) )
        {
            return $str;
        }

        $str = ' ' . $str . ' ';

        // \w, \b and a few others do not match on a unicode character
        // set for performance reasons. As a result words like �ber
        // will not match on a word boundary. Instead, we'll assume that
        // a bad word will be bookeneded by any of these characters.
        $delim = '[-_\'\"`(){}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

        foreach( $censored as $badword )
        {
            if( $replacement !== '' )
            {
                $str = preg_replace( "/({$delim})(" . str_replace( '\*', '\w*?', preg_quote( $badword, '/' ) ) . ")({$delim})/i", "\\1{$replacement}\\3", $str );
            }
            else
            {
                $str = preg_replace( "/({$delim})(" . str_replace( '\*', '\w*?', preg_quote( $badword, '/' ) ) . ")({$delim})/ie", "'\\1'.str_repeat('#', strlen('\\2')).'\\3'", $str );
            }
        }

        return trim( $str );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'highlight_code' ) )
{
    /**
     * Code Highlighter
     *
     * Colorizes code strings
     *
     * @param    string    the text string
     *
     * @return    string
     */
    function highlight_code( $str )
    {
        /* The highlight string function encodes and highlights
         * brackets so we need them to start raw.
         *
         * Also replace any existing PHP tags to temporary markers
         * so they don't accidentally break the string out of PHP,
         * and thus, thwart the highlighting.
         */
        $str = str_replace(
            array( '&lt;', '&gt;', '<?', '?>', '<%', '%>', '\\', '</script>' ),
            array( '<', '>', 'phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose' ),
            $str
        );

        // The highlight_string function requires that the text be surrounded
        // by PHP tags, which we will remove later
        $str = highlight_string( '<?php ' . $str . ' ?>', TRUE );

        // Remove our artificially added PHP, and the syntax highlighting that came with it
        $str = preg_replace(
            array(
                '/<span style="color: #([A-Z0-9]+)">&lt;\?php(&nbsp;| )/i',
                '/(<span style="color: #[A-Z0-9]+">.*?)\?&gt;<\/span>\n<\/span>\n<\/code>/is',
                '/<span style="color: #[A-Z0-9]+"\><\/span>/i'
            ),
            array(
                '<span style="color: #$1">',
                "$1</span>\n</span>\n</code>",
                ''
            ),
            $str
        );

        // Replace our markers back to PHP tags.
        return str_replace(
            array( 'phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose' ),
            array( '&lt;?', '?&gt;', '&lt;%', '%&gt;', '\\', '&lt;/script&gt;' ),
            $str
        );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'highlight_phrase' ) )
{
    /**
     * Phrase Highlighter
     *
     * Highlights a phrase within a text string
     *
     * @param    string $str       the text string
     * @param    string $phrase    the phrase you'd like to highlight
     * @param    string $tag_open  the openging tag to precede the phrase with
     * @param    string $tag_close the closing tag to end the phrase with
     *
     * @return    string
     */
    function highlight_phrase( $str, $phrase, $tag_open = '<mark>', $tag_close = '</mark>' )
    {
        return ( $str !== '' && $phrase !== '' )
            ? preg_replace( '/(' . preg_quote( $phrase, '/' ) . ')/i' . ( UTF8_ENABLED ? 'u' : '' ), $tag_open . '\\1' . $tag_close, $str )
            : $str;
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'convert_accented_characters' ) )
{
    /**
     * Convert Accented Foreign Characters to ASCII
     *
     * @param    string $str Input string
     *
     * @return    string
     */
    function convert_accented_characters( $str )
    {
        static $array_from, $array_to;

        if( ! is_array( $array_from ) )
        {
            if( is_file( APPSPATH . 'config/foreign_chars.php' ) )
            {
                include( APPSPATH . 'config/foreign_chars.php' );
            }

            if( is_file( APPSPATH . 'config/' . ENVIRONMENT . '/foreign_chars.php' ) )
            {
                include( APPSPATH . 'config/' . ENVIRONMENT . '/foreign_chars.php' );
            }

            if( empty( $foreign_characters ) OR ! is_array( $foreign_characters ) )
            {
                $array_from = array();
                $array_to = array();

                return $str;
            }

            $array_from = array_keys( $foreign_characters );
            $array_to = array_values( $foreign_characters );
        }

        return preg_replace( $array_from, $array_to, $str );
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'word_wrap' ) )
{
    /**
     * Word Wrap
     *
     * Wraps text at the specified character. Maintains the integrity of words.
     * Anything placed between {unwrap}{/unwrap} will not be word wrapped, nor
     * will URLs.
     *
     * @param    string $str     the text string
     * @param    int    $charlim = 76    the number of characters to wrap at
     *
     * @return    string
     */
    function word_wrap( $str, $charlim = 76 )
    {
        // Set the character limit
        is_numeric( $charlim ) OR $charlim = 76;

        // Reduce multiple spaces
        $str = preg_replace( '| +|', ' ', $str );

        // Standardize newlines
        if( strpos( $str, "\r" ) !== FALSE )
        {
            $str = str_replace( array( "\r\n", "\r" ), "\n", $str );
        }

        // If the current word is surrounded by {unwrap} tags we'll
        // strip the entire chunk and replace it with a marker.
        $unwrap = array();
        if( preg_match_all( '|\{unwrap\}(.+?)\{/unwrap\}|s', $str, $matches ) )
        {
            for( $i = 0, $c = count( $matches[ 0 ] ); $i < $c; $i++ )
            {
                $unwrap[ ] = $matches[ 1 ][ $i ];
                $str = str_replace( $matches[ 0 ][ $i ], '{{unwrapped' . $i . '}}', $str );
            }
        }

        // Use PHP's native function to do the initial wordwrap.
        // We set the cut flag to FALSE so that any individual words that are
        // too long get left alone. In the next step we'll deal with them.
        $str = wordwrap( $str, $charlim, "\n", FALSE );

        // Split the string into individual lines of text and cycle through them
        $output = '';
        foreach( explode( "\n", $str ) as $line )
        {
            // Is the line within the allowed character count?
            // If so we'll join it to the output and continue
            if( mbStrlen( $line ) <= $charlim )
            {
                $output .= $line . "\n";
                continue;
            }

            $temp = '';
            while( mbStrlen( $line ) > $charlim )
            {
                // If the over-length word is a URL we won't wrap it
                if( preg_match( '!\[url.+\]|://|www\.!', $line ) )
                {
                    break;
                }

                // Trim the word down
                $temp .= mbSubstr( $line, 0, $charlim - 1 );
                $line = mbSubstr( $line, $charlim - 1 );
            }

            // If $temp contains data it means we had to split up an over-length
            // word into smaller chunks so we'll add it back to our current line
            if( $temp !== '' )
            {
                $output .= $temp . "\n" . $line . "\n";
            }
            else
            {
                $output .= $line . "\n";
            }
        }

        // Put our markers back
        if( count( $unwrap ) > 0 )
        {
            foreach( $unwrap as $key => $val )
            {
                $output = str_replace( '{{unwrapped' . $key . '}}', $val, $output );
            }
        }

        return $output;
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'ellipsize' ) )
{
    /**
     * Ellipsize String
     *
     * This function will strip tags from a string, split it at its max_length and ellipsize
     *
     * @param    string    string to ellipsize
     * @param    int       max length of string
     * @param    mixed     int (1|0) or float, .5, .2, etc for position to split
     * @param    string    ellipsis ; Default '...'
     *
     * @return    string    ellipsized string
     */
    function ellipsize( $str, $max_length, $position = 1, $ellipsis = '&hellip;' )
    {
        // Strip tags
        $str = trim( strip_tags( $str ) );

        // Is the string long enough to ellipsize?
        if( mbStrlen( $str ) <= $max_length )
        {
            return $str;
        }

        $beg = mbSubstr( $str, 0, floor( $max_length * $position ) );
        $position = ( $position > 1 ) ? 1 : $position;

        if( $position === 1 )
        {
            $end = mbSubstr( $str, 0, -( $max_length - mbStrlen( $beg ) ) );
        }
        else
        {
            $end = mbSubstr( $str, -( $max_length - mbStrlen( $beg ) ) );
        }

        return $beg . $ellipsis . $end;
    }
}
