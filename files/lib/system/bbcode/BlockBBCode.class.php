<?php namespace wcf\system\bbcode;

use wcf\data\regulation\Regulation;
use wcf\util\StringUtil;
use wcf\system\request\LinkHandler;

class BlockBBCode extends AbstractBBCode 
{

	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) 
	{
		$content = StringUtil::trim($content);
		$html = <<<UNKNOWNSOLDIERS
<div>$content</div>
UNKNOWNSOLDIERS;

		return $html;
	}
	
}
