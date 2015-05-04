<?php if ( ! defined('BASEPATH')) exit('Direct access forbidden.');

function transformBBcode($content)
{
	$bbcodeHelperBBcodes		= [
		'b'						=> '<strong>{{bbcodeContent}}</strong>',
		'i'						=> '<em>{{bbcodeContent}}</em>',
		'url'					=> '<a href="{{url}}" type="button">{{bbcodeContent}}</a>',
		'img'					=> '<img src="{{bbcodeContent}}" />',
		'code'					=> '<pre>{{bbCodeColoredContent}}</pre>',
	];

	$bbcodeFound		= false;
	$transformedContent	= $content;
	$searchKeywords		= [];
	do
	{
		foreach($bbcodeHelperBBcodes as $bbcodeTypeKey => $bbcodeTypeValue)
		{
			$pattern		= '/\[' . $bbcodeTypeKey . '([^\]]*)\](.*?)\[\/' . $bbcodeTypeKey . '\]/s';
			if(preg_match($pattern, $transformedContent, $matches))
			{
				if($bbcodeTypeKey == 'searchkeyword')
				{
					$searchKeywords[]		= $matches[2];
				}
				$bbcodeFound		= true;
				$transformedContent	= getBBcodeContent($transformedContent, $bbcodeTypeValue, $matches, $bbcodeTypeKey);
				break;
			}else{
				$bbcodeFound		= false;
			}
		}

	}while($bbcodeFound);

	$response					= new stdClass();
	$response->content			= $transformedContent;
	$response->searchKeywords	= $searchKeywords;
	return $response;
}

function getBBcodeContent($content, $replaced, $matches, $name)
{
	$bbcodeParams			= [];
	if(!empty($matches[1]))
	{
		if(preg_match_all('/([a-zA-Z]+)\=\"([^"]*)\"/s', $matches[1], $matchedParameters))
		{
			for($i = 0; $i < count($matchedParameters[1]); $i++)
			{
				$paramKey							= $matchedParameters[1][$i];
				$bbcodeParams[$paramKey]			= $matchedParameters[2][$i];
			}
		}
	}

	$transformedContent					= $content;
	$parameterHtml						= $replaced;

	if(preg_match_all('/\{\{(?P<name>\w+)\}\}/s', $parameterHtml, $replacedKeys))
	{

		foreach($replacedKeys['name'] as $replaceKey)
		{
			if($replaceKey == 'bbcodeContent') {
				$parameterHtml = preg_replace('/\{\{' . $replaceKey . '\}\}/s', $matches[2], $parameterHtml);
			}elseif($replaceKey == 'bbCodeColoredContent') {
				$newLineString			= strtr($matches[2], array('<div>' => "\n", '</div>'=>''));
				$removedHtmlString		= preg_replace('/\&(\w+)\;/s', ' ', "<?\n" . strip_tags($newLineString) . "\n?>");
				$highlightedSyntax		= highlight_string($removedHtmlString, true);
				$parameterHtml = preg_replace('/\{\{' . $replaceKey . '\}\}/s', $highlightedSyntax, $parameterHtml);
			}else{
				$parameterHtml			= preg_replace('/\{\{'. $replaceKey .'\}\}/s', $bbcodeParams[$replaceKey], $parameterHtml);
			}
		}
	}

	$transformedContent			= str_replace($matches[0], $parameterHtml, $transformedContent);
	return $transformedContent;
}