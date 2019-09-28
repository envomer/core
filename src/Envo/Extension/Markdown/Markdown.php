<?php

namespace Envo\Extension\Markdown;

class Markdown extends \Parsedown
{
	/**
	 * @param $md
	 * @param array $options
	 *
	 * @return string|string[]|null
	 * @credits https://github.com/stiang
	 */
	public function strip($md, $options = [])
	{
		// char to insert instead of stripped list leaders (default: '')
		$options['listUnicodeChar'] = isset( $options['listUnicodeChar'] ) ? $options['listUnicodeChar'] : false;
		// strip list leaders (default: true)
		$options['stripListLeaders'] = isset( $options['stripListLeaders'] ) ? $options['stripListLeaders'] : true;
		// support GitHub-Flavored Markdown (default: true)
		$options['gfm'] = isset( $options['gfm'] ) ? $options['gfm'] : true;
		// replace images with alt-text, if present (default: true)
		$options['useImgAltText'] = isset( $options['useImgAltText'] ) ? $options['useImgAltText'] : true;
		$output = $md;
		$output = preg_replace( '/^(-\s*?|\*\s*?|_\s*?){3,}\s*$/m', '', $output );
		
		try {
			if( $options['stripListLeaders'] ) {
				if( $options['listUnicodeChar'] ) {
					$output = preg_replace( '/^([\s\t]*)([\*\-\+]|\d+\.)\s+/m', $options['listUnicodeChar'].' $1', $output );
				}
				else {
					$output = preg_replace( '/^([\s\t]*)([\*\-\+]|\d+\.)\s+/m', '$1', $output );
				}
			}
			if( $options['gfm'] ) {
				// Header
				$output = preg_replace( '/\n={2,}/', '\n', $output );
				// Fenced codeblocks
				$output = preg_replace( '/~{3}.*\n/', '', $output );
				// Strikethrough
				$output = preg_replace( '/~~/', '', $output );
				// Fenced codeblocks
				$output = preg_replace( '/`{3}.*\n/', '', $output );
			}
			// Remove HTML tags
			$output = preg_replace( '/<[^>]*>/', '', $output );
			// Remove setext-style headers
			$output = preg_replace( '/^[=\-]{2,}\s*$/', '', $output );
			// Remove footnotes?
			$output = preg_replace( '/\[\^.+?\](\: .*?$)?/', '', $output );
			$output = preg_replace( '/\s{0,2}\[.*?\]: .*?$/', '', $output );
			// Remove images
			$output = preg_replace( '/\!\[(.*?)\][\[\(].*?[\]\)]/', ( $options['useImgAltText'] ? '$1' : '' ), $output );
			// Remove inline links
			$output = preg_replace( '/\[(.*?)\][\[\(].*?[\]\)]/', '$1', $output );
			// Remove blockquotes
			$output = preg_replace( '/^\s{0,3}>\s?/', '', $output );
			// Remove reference-style links?
			$output = preg_replace( '/^\s{1,2}\[(.*?)\]: (\S+)( ".*?")?\s*$/', '', $output );
			// Remove atx-style headers
			$output = preg_replace( '/^(\n)?\s{0,}#{1,6}\s+| {0,}(\n)?\s{0,}#{0,} {0,}(\n)?\s{0,}$/m', '$1$2$3', $output );
			// Remove emphasis (repeat the line to remove double emphasis)
			$output = preg_replace( '/([\*_]{1,3})(\S.*?\S{0,1})\1/', '$2', $output );
			$output = preg_replace( '/([\*_]{1,3})(\S.*?\S{0,1})\1/', '$2', $output );
			// Remove code blocks
			$output = preg_replace( '/(`{3,})(.*?)\1/m', '$2', $output );
			// Remove inline code
			$output = preg_replace( '/`(.+?)`/', '$1', $output );
			// Replace two or more newlines with exactly two? Not entirely sure this belongs here...
			$output = preg_replace( '/\n{2,}/', '\n\n', $output );
		} catch( Exception $e ) {
			return $md;
		}
		
		return $output;
	}
}