<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 05-Aug-16
 * Time: 4:06 AM
 */

namespace O2System\Core\HTML;

use O2System\Core\HTML\DOM\Indenter;
use O2System\Core\HTML\DOM\Parser;
use O2System\Core\HTML\Interfaces\DOCTYPE;
use O2System\Core\Traits\Constant;

/**
 * Class Document
 *
 * @see     http://www.w3schools.com/tags/tag_doctype.asp
 * @see     http://www.w3schools.com/tags/ref_html_dtd.asp
 *
 * @package O2System\Core\HTML
 */
class Document extends \O2System\Core\File\Document implements DOCTYPE
{
	use Constant;

	public    $DOMDocument;
	protected $doctype;
	protected $encoding;

	/**
	 * Document constructor.
	 */
	public function __construct()
	{
		$this->setDocType( Document::HTML5 );
		$this->setMimeType( Document::TEXT_HTML );
		$this->setEncoding( 'UTF-8' );

		$this->DOMDocument = new \DOMDocument( 5, 'UTF-8' );
	}

	public function setDocType( $doctype )
	{
		if ( in_array( $doctype, static::getConstants() ) )
		{
			$this->doctype = $doctype;
		}

		return $this;
	}

	public function setEncoding( $encoding )
	{
		$this->encoding = strtoupper( $encoding );
	}

	public function loadFile( $filepath )
	{
		if ( is_file( $filepath ) )
		{
			libxml_use_internal_errors( TRUE );

			$this->DOMDocument->loadHTMLFile( $filepath, LIBXML_HTML_NODEFDTD );
		}
	}

	public function loadString( $source )
	{
		if ( $this->encoding === 'UTF-8' )
		{
			$source = mb_convert_encoding( $source, 'HTML-ENTITIES', 'UTF-8' );
		}

		libxml_use_internal_errors( TRUE );
		$this->DOMDocument->loadHTML( $source, LIBXML_HTML_NODEFDTD );
	}

	public function getDOMParser()
	{
		$DOMParser = new Parser();
		$DOMParser->load( $this->save() );

		return $DOMParser;
	}

	/**
	 * @param null  $filename
	 * @param array $options
	 *
	 * @return mixed|string
	 * @throws \Gajus\Dindent\Exception\RuntimeException
	 */
	public function save( array $options = [ 'beautify' => FALSE ] )
	{
		$this->DOMDocument->preserveWhiteSpace = FALSE;
		$this->DOMDocument->formatOutput       = TRUE;

		$html = $this->DOMDocument->saveHTML();
		$html = preg_replace( [ '/^<!DOCTYPE.+?>/', '/^<!doctype.+?>/' ], '', $html );
		$html = preg_replace( '/<html.+?>/', '<html>', $html );

		switch ( $this->doctype )
		{
			default:
			case self::HTML5:

				$DOCTYPE  = 'html';
				$publicId = NULL;
				$systemId = NULL;

				$html = html_entity_decode( $html, ENT_HTML5, $this->encoding );

				break;

			case self::HTML4_STRICT:

				$DOCTYPE  = 'HTML';
				$publicId = '-//W3C//DTD HTML 4.01//EN';
				$systemId = 'http://www.w3.org/TR/html4/strict.dtd';

				$html = html_entity_decode( $html, ENT_HTML401, $this->encoding );

				break;

			case self::HTML4_TRANSITIONAL:

				$DOCTYPE  = 'HTML';
				$publicId = '-//W3C//DTD HTML 4.01 Transitional//EN';
				$systemId = 'http://www.w3.org/TR/html4/loose.dtd';

				$html = html_entity_decode( $html, ENT_HTML401, $this->encoding );

				break;

			case self::HTML4_FRAMESET:

				$DOCTYPE  = 'HTML';
				$publicId = '-//W3C//DTD HTML 4.01 Frameset//EN';
				$systemId = 'http://www.w3.org/TR/html4/frameset.dtd';

				$html = html_entity_decode( $html, ENT_HTML401, $this->encoding );

				break;

			case self::XHTML1_STRICT:

				$DOCTYPE  = 'html';
				$publicId = '-//W3C//DTD XHTML 1.0 Strict//EN';
				$systemId = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd';

				$html = html_entity_decode( $html, ENT_XHTML, $this->encoding );

				break;

			case self::XHTML1_TRANSITIONAL:

				$DOCTYPE  = 'html';
				$publicId = '-//W3C//DTD XHTML 1.0 Transitional//EN';
				$systemId = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd';

				$html = html_entity_decode( $html, ENT_XHTML, $this->encoding );

				break;

			case self::XHTML1_FRAMESET:

				$DOCTYPE  = 'html';
				$publicId = '-//W3C//DTD XHTML 1.0 Frameset//EN';
				$systemId = 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd';

				$html = html_entity_decode( $html, ENT_XHTML, $this->encoding );

				break;

			case self::XHTML11:

				$DOCTYPE  = 'html';
				$publicId = '-//W3C//DTD XHTML 1.1//EN';
				$systemId = 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd';

				$html = html_entity_decode( $html, ENT_XHTML, $this->encoding );

				break;
		}

		$attributesDOCTYPE[] = $DOCTYPE;
		$attributesDOCTYPE[] = empty( $publicId ) ? '' : 'PUBLIC ' . $publicId;
		$attributesDOCTYPE[] = empty( $systemId ) ? '' : $systemId;

		$attributesDOCTYPE = array_filter( $attributesDOCTYPE );

		$html = '<!DOCTYPE ' . implode( ' ', $attributesDOCTYPE ) . '>' . PHP_EOL . $html;

		if ( isset( $options[ 'beautify' ] ) AND $options[ 'beautify' ] === TRUE )
		{
			$DOMIndenter = new Indenter();

			// Beautify
			$html = $DOMIndenter->indent( $html );
		}

		return trim( $html );
	}

	public function __toString()
	{
		return $this->save();
	}
}