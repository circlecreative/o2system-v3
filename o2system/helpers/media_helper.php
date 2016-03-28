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
 * Media Helpers
 *
 * @package        O2System
 * @subpackage     helpers
 * @category       Helpers
 * @author         Circle Creative Dev Team
 * @link           http://circle-creative.com/products/o2system-codeigniter/user-guide/helpers/media.html
 */
// ------------------------------------------------------------------------

/**
 * Media
 *
 * View Media
 *
 * @access    public
 *
 * @param    string
 *
 * @return    string
 */
if( ! function_exists( 'player_media' ) )
{
    function player_media( $filename, $path = '', $properties )
    {
        $system = &O2System::instance();
        $system->load->helper( array( 'string', 'assets' ) );

        assets_jquery( array( 'vplayer', 'swfobject' ), 'media-player' );

        $id = random_string( 'alnum', 8 );
        $id = strtolower( $id );
        $info = pathinfo( $filename );
        $type = strtolower( $info[ 'extension' ] );
        $folder = ( $path == '' ? $type : $path );
        $allowed_media = array(
            'mp3',
            'wav',
            'flv',
            'mov',
            'mp4',
            'wmv',
            'avi'
        );
        if( in_array( $type, $allowed_media ) )
        {
            switch( $type )
            {
                case 'flv' :
                    $media = '
					<!-- FLV Player :: START -->

					<object id="player" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" name="player" width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '">
						<param name="movie" value="' . assets_url() . 'jquery/swf/player.swf" />
						<param name="allowfullscreen" value="true" />
						<param name="allowscriptaccess" value="always" />
						<param name="flashvars" value="file=' . media_url() . $folder . '/' . $filename . '&image=' . element( 'preview', $properties ) . '" />
						<embed
							type="application/x-shockwave-flash"
							id="' . $id . '"
							name="' . $id . '"
							src="' . assets_url() . 'jquery/swf/player.swf"
							width="' . $properties[ 'width' ] . '"
							height="' . $properties[ 'height' ] . '"
							allowscriptaccess="always"
							allowfullscreen="true"
							flashvars="file=' . media_url() . $folder . '/' . $filename . '&image=' . element( 'preview', $properties ) . '"
						/>
					</object>

					<!-- FLV Player :: END -->';
                    break;
                case 'wmv' :
                case 'mp4' :
                case 'avi' :
                    $media = '
					<!-- Media Player :: START -->

					<object id=\'mediaPlayer\' width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '"
					classid=\'clsid:22d6f312-b0f6-11d0-94ab-0080c74c7e95\'
					codebase=\'http://activex.microsoft.com/activex/controls/ mplayer/en/nsmp2inf.cab#Version=5,1,52,701\'
					standby=\'Loading Microsoft Windows Media Player components...\' type=\'application/x-oleobject\'>
					<param name=\'fileName\' value="' . media_url() . $type . '/' . $filename . '">
					<param name=\'animationatStart\' value=\'1\'>
					<param name=\'transparentatStart\' value=\'1\'>
					<param name=\'autoStart\' value=\'1\'>
					<param name=\'ShowControls\' value=\'0\'>
					<param name=\'ShowDisplay\' value=\'0\'>
					<param name=\'ShowStatusBar\' value=\'0\'>
					<param name=\'loop\' value=\'0\'>
					<embed type=\'application/x-mplayer2\'
					pluginspage=\'http://microsoft.com/windows/mediaplayer/en/download/\'
					id=\'mediaPlayer\' name=\'mediaPlaye\r\' displaysize=\'4\' autosize=\'1\'
					bgcolor=\'darkblue\' showcontrols=\'0\' showtracker=\'0\'
					showdisplay=\'0\' showstatusbar=\'0\' videoborder3d=\'0\' width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '"
					src="' . media_url() . $type . '/' . $filename . '" autostart=\'0\' designtimesp=\'5311\' loop=\'0\'>
					</embed>
					</object>

					<!-- Media Player :: END -->';
                    break;
                case 'mov' :
                    $media = '
					<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="' . $properties[ 'height' ] . '" width="' . $properties[ 'width' ] . '">

					<param name="src" value="' . media_url() . $type . '/' . $filename . '">
					<param name="autoplay" value="true">
					<param name="type" value="video/quicktime" height="' . $properties[ 'height' ] . '" width="' . $properties[ 'width' ] . '">

					<embed src="' . media_url() . $type . '/' . $filename . '" height="' . $properties[ 'height' ] . '" width="' . $properties[ 'width' ] . '" autoplay="true" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/">

					</object>
					';
                    break;
            }

            return $media;
        }
        else
        {
            return 'Unrecognized Media Type';
        }
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'url_media' ) )
{
    /**
     * Media
     *
     * View Media
     *
     * @access    public
     *
     * @param    string
     *
     * @return    string
     */
    function url_media( $url, $properties )
    {
        $system =& O2System::instance();
        $system->load->helper( 'string' );
        $system->load->helper( 'assets' );
        $id = random_string( 'alnum', 8 );
        $id = strtolower( $id );
        if( preg_match( '/\byoutube\b/i', $url ) )
        {
            $type = 'youtube';
            $query = parse_url( $url, PHP_URL_QUERY );
            $query = parse_url( $url, PHP_URL_QUERY );
            parse_str( $query, $query );
            $video = 'http://www.youtube.com/v/' . $query[ 'v' ] . '?fs=1&amp;hl=en_US';
        }
        elseif( preg_match( '/\bmetacafe\b/i', $url ) )
        {
            $type = 'metacafe';
            $query = parse_url( $url, PHP_URL_PATH );
            $query = str_replace( '/watch/', '', $query );
            $query = explode( '/', $query, 2 );
            $video = 'http://www.metacafe.com/fplayer/' . reset( $query ) . '/' . str_replace( '/', '', end( $query ) ) . '.swf';
        }
        elseif( preg_match( '/\bvimeo\b/i', $url ) )
        {
            $type = 'vimeo';
            $query = parse_url( $url, PHP_URL_PATH );
            $video = str_replace( '/', '', $query );
        }
        elseif( preg_match( '/\bfacebook\b/i', $url ) )
        {
            $type = 'facebook-video';
            $query = parse_url( $url, PHP_URL_QUERY );
            parse_str( $query, $query );
            $video = 'http://www.facebook.com/v/' . $query[ 'v' ];
        }
        elseif( preg_match( '/\bgoogle\b/i', $url ) )
        {
            $type = 'google-video';
            $query = parse_url( $url, PHP_URL_QUERY );
            parse_str( $query, $query );
            $video = 'http://video.google.com/googleplayer.swf?docId=' . $query[ 'docid' ] . '&hl=en';
        }
        elseif( preg_match( '/\byahoo\b/i', $url ) )
        {
            $type = 'yahoo-video';
            $query = parse_url( $url, PHP_URL_PATH );
            $query = str_replace( '/watch/', '', $query );
            $query = explode( '/', $query, 2 );
            $video[ 'id' ] = end( $query );
            $video[ 'vid' ] = reset( $query );
        }
        elseif( preg_match( '/\bmyspace\b/i', $url ) )
        {
            $type = 'myspace-video';
            $query = parse_url( $url, PHP_URL_PATH );
            $query = str_replace( '/video/vid/', '', $query );
            $video = str_replace( '/', '', $query );
        }
        $allowed_media = array(
            'youtube',
            'metacafe',
            'vimeo',
            'facebook-video',
            'google-video',
            'yahoo-video',
            'myspace-video'
        );
        if( in_array( $type, $allowed_media ) )
        {
            switch( $type )
            {
                case 'youtube' :
                    $media = '
					<!-- Youtube Player :: START -->
					<object width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '">
						<param name="movie" value="' . $video . '"></param>
						<param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>
						<embed src="' . $video . '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '"></embed>
					</object>
					<!-- Youtube Player :: END -->';
                    break;
                case 'vimeo' :
                    $media = '
					<!-- Vimeo Player :: START -->
					<object width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '">
						<param name="allowfullscreen" value="true" />
						<param name="allowscriptaccess" value="always" />
						<param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=' . $video . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" />

						<embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $video . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="' .
                             $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '">
						</embed>
					</object>
					<!-- Vimeo Player :: END -->';
                    break;
                case 'metacafe' :
                    $media = '
					<!-- Metacafe Player :: START -->
					<embed src="' . $video . '" width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] .
                             '" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowFullScreen="true" allowScriptAccess="always" name="' . $id . '"></embed>
					<!-- Metacafe Player :: END -->
					';
                    break;
                case 'facebook-video' :
                    $media = '
					<!-- Facebook Video Player :: START -->
					<object width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '" >
						<param name="allowfullscreen" value="true" />
						<param name="allowscriptaccess" value="always" />
						<param name="movie" value="' . $video . '" />
						<embed src="' . $video . '" type="application/x-shockwave-flash"
						allowscriptaccess="always" allowfullscreen="true" width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '">
						</embed>
					</object>
					<!-- Facebook Video Player :: END -->
					';
                    break;
                case 'google-video' :
                    $media = '
					<!-- Google Video Player :: START -->
					<embed style="width:' . $properties[ 'width' ] . 'px; height:' . $properties[ 'height' ] . 'px;" id="VideoPlayback" type="application/x-shockwave-flash"
					src="' . $video . '"></embed>
					<!-- Google Video Player :: END -->
					';
                    break;
                case 'yahoo-video' :
                    $media = '
					<!-- Yahoo! Video Player :: START -->
					<object width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '">
						<param name="movie" value="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" />
						<param name="allowFullScreen" value="true" /><param name="AllowScriptAccess" value="always" />
						<param name="bgcolor" value="#000000" />
						<param name="flashVars" value="id=' . $video[ 'id' ] . '&vid=' . $video[ 'vid' ] . '&lang=en-us&intl=us&thumbUrl=&embed=1" />
						<embed src="http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.46" type="application/x-shockwave-flash" width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '" allowFullScreen="true" AllowScriptAccess="always" bgcolor="#000000" flashVars="id=' . $video[ 'id' ] .
                             '&vid=' . $video[ 'vid' ] . '&lang=en-us&intl=us&thumbUrl=&embed=1" ></embed>
					</object>
					<!-- Yahoo! Video Player :: END -->
					';
                    break;
                case 'myspace-video' :
                    $media = '
					<!-- MySpace Video Player :: START -->
					<object width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '" >
						<param name="allowScriptAccess" value="always"/>
						<param name="allowFullScreen" value="true"/>
						<param name="wmode" value="transparent"/>
						<param name="movie" value="http://mediaservices.myspace.com/services/media/embed.aspx/m=' . $video . ',t=1,mt=video"/>
						<embed src="http://mediaservices.myspace.com/services/media/embed.aspx/m=' . $video . ',t=1,mt=video" width="' . $properties[ 'width' ] . '" height="' . $properties[ 'height' ] . '" allowFullScreen="true" type="application/x-shockwave-flash" wmode="transparent" allowScriptAccess="always"></embed>
					</object>
					<!-- MySpace Video Player :: END -->
					';
                    break;
            }

            return $media;
        }
        else
        {
            return 'Unrecognized Media URL';
        }
    }
}

// ------------------------------------------------------------------------

if( ! function_exists( 'embed_media' ) )
{
    /**
     * Media
     *
     * View Media
     *
     * @param    string
     *
     * @return    string
     */
    function embed_media( $script, $properties )
    {
        $pattern = array(
            '~(width=")(\d+)"~',
            '~(height=")(\d+)"~',
            '~(width:)(0-9)(px;)~',
            '~(height:)(0-9)(px;)~'
        );
        $replace = array(
            'width="' . $properties[ 'width' ] . '"',
            'height="' . $properties[ 'height' ] . '"',
            'width: ' . $properties[ 'width' ] . 'px;',
            'height: ' . $properties[ 'height' ] . 'px;'
        );

        return preg_replace( $pattern, $replace, $script );
    }
}
