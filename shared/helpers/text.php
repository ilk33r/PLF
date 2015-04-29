<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

/**
* ------------------------------------------------
* Word limit function
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $text
* @param int $wordCount
* @return string
* 
*/

if(!function_exists('wordLimit'))
{
	function wordLimit($text, $wordCount)
	{
		$words				= explode(' ', $text);
		$limitedText		= '';
		$activeWordCount	= 0;
		foreach($words as $word)
		{
			if($activeWordCount < $wordCount)
			{
				$limitedText	.= $word.' ';
				$activeWordCount++;
			}else{
				break;
			}
		}
		return $limitedText;
	}
}


/**
* ------------------------------------------------
* Caharacter limit function
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $text
* @param int $characterCount
* @return string
* 
*/

if(!function_exists('characterLimit'))
{
	function characterLimit($text, $characterCount)
	{
		$words					= explode(' ', $text);
		$limitedText			= '';
		$activeCharacterCount	= 0;
		foreach($words as $word)
		{
			if($activeCharacterCount < $characterCount)
			{
				$limitedText		.= $word.' ';
				$activeCharacterCount	+= strlen($word) + 1;
			}else{
				break;
			}
		}
		return $limitedText;
	}
}


/**
* ------------------------------------------------
* Convert Foreign Characters function
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $text
* @return string
* 
*/

if(!function_exists('convertForeignCharacters'))
{
	function convertForeignCharacters($text)
	{
		static $foreignCharacters	= array(
							'/ä|æ|ǽ/'								=> 'ae',
							'/œ/'									=> 'oe',
							'/Ä/'									=> 'Ae',
							'/À|Á|Â|Ã|Ä|Å|Ǻ|Ā|Ă|Ą|Ǎ/'				=> 'A',
							'/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/'				=> 'a',
							'/Ç|Ć|Ĉ|Ċ|Č/'							=> 'C',
							'/ç|ć|ĉ|ċ|č/'							=> 'c',
							'/Ð|Ď|Đ/'								=> 'D',
							'/ð|ď|đ/'								=> 'd',
							'/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/'					=> 'E',
							'/è|é|ê|ë|ē|ĕ|ė|ę|ě/'					=> 'e',
							'/Ĝ|Ğ|Ġ|Ģ/'								=> 'G',
							'/ĝ|ğ|ġ|ģ/'								=> 'g',
							'/Ĥ|Ħ/'									=> 'H',
							'/ĥ|ħ/'									=> 'h',
							'/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/'					=> 'I',
							'/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/'					=> 'i',
							'/Ĵ/'									=> 'J',
							'/ĵ/'									=> 'j',
							'/Ķ/'									=> 'K',
							'/ķ/'									=> 'k',
							'/Ĺ|Ļ|Ľ|Ŀ|Ł/'							=> 'L',
							'/ĺ|ļ|ľ|ŀ|ł/'							=> 'l',
							'/Ñ|Ń|Ņ|Ň/'								=> 'N',
							'/ñ|ń|ņ|ň|ŉ/'							=> 'n',
							'/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|Ö/'				=> 'O',
							'/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|ö/'			=> 'o',
							'/Ŕ|Ŗ|Ř/'								=> 'R',
							'/ŕ|ŗ|ř/'								=> 'r',
							'/Ś|Ŝ|Ş|Š/'								=> 'S',
							'/ś|ŝ|ş|š|ſ/'							=> 's',
							'/Ţ|Ť|Ŧ/'								=> 'T',
							'/ţ|ť|ŧ/'								=> 't',
							'/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|Ü/'		=> 'U',
							'/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|ü/'		=> 'u',
							'/Ý|Ÿ|Ŷ/'								=> 'Y',
							'/ý|ÿ|ŷ/'								=> 'y',
							'/Ŵ/'									=> 'W',
							'/ŵ/'									=> 'w',
							'/Ź|Ż|Ž/'								=> 'Z',
							'/ź|ż|ž/'								=> 'z',
							'/Æ|Ǽ/'									=> 'AE',
							'/ß/'									=> 'ss',
							'/Ĳ/'									=> 'IJ',
							'/ĳ/'									=> 'ij',
							'/Œ/'									=> 'OE',
							'/ƒ/'									=> 'f'
						);
						

		return preg_replace(array_keys($foreignCharacters), array_values($foreignCharacters), $text);
	}
}


/**
* ------------------------------------------------
* Convert SEO URI function
* ------------------------------------------------
* 
* @author ilker özcan
* @param string $text
* @return string
* 
*/

if(!function_exists('slugifyText'))
{
	function slugifyText($text)
	{
		$convertedText		= convertForeignCharacters($text);
		$permitedCharacters	= 'a-z A-Z0-9\_\-';
		$pattern			= '/^['.$permitedCharacters.'\/]+$/';
		$resultSEO			= '';
		
		for($i = 0; $i < strlen($convertedText); $i++)
		{
			$subStringText	= substr($convertedText, $i, 1);
			$match			= preg_match($pattern, $subStringText, $out);
			if($match)
			{
				$resultSEO	.= $subStringText;
			}else{
				$resultSEO	.= '';
			}
		}
		
		return urlencode(strtolower(trim(strtr($resultSEO, array(' '=>'-')))));
	}
}



/**
* ------------------------------------------------
* End of file text.php
* ------------------------------------------------
*/