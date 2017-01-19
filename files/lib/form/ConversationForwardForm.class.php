<?php
namespace wcf\form;
use wcf\data\conversation\message\ConversationMessage;
use wcf\system\conversation\ConversationHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\UserInputException;
use wcf\data\IMessage;
use wcf\system\message\quote\MessageQuoteManager;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows the conversation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.conversation
 * @subpackage	form
 * @category	Community Framework
 */
class ConversationForwardForm extends ConversationAddForm {
	public $preMessageIDs = array();
	
	public $preConversationID = -1;
	
	public $preMessageContentFormatted = "";
	
	public $preSubject = "";
	
	public $enableHtml = false;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check for conversation id
		if (isset($_GET["id"])) {
			$this->preConversationID = intval($_GET["id"]);
		} else {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.conversation.forward.error.invalidInputID'));
		}
		
		// validate current user is participant in the conversation
		$requesterUserID = WCF::getUser()->userID;
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_conversation_to_user
			WHERE	participantID = ?
			AND 	conversationID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($requesterUserID, $this->preConversationID));
		$row = $statement->fetchArray();
		if ($row !== false) { }
		else {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.conversation.forward.error.invalidParticipation'));
		}
		
		// get the subject
		$sql = "SELECT   subject
			FROM     wcf".WCF_N."_conversation
			WHERE    conversationID = ?
			LIMIT 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->preConversationID));
		while ($row = $statement->fetchArray()) {
			$this->preSubject = "FW: ".$row["subject"];
		}
		
		// get messages
		$sql = "SELECT   messageID, username, message, time
			FROM     wcf".WCF_N."_conversation_message
			WHERE    conversationID = ?
			ORDER BY time";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->preConversationID));
		while ($row = $statement->fetchArray()) {
			$this->preMessageIDs[] = $row["messageID"];
			
			$dateTime = DateUtil::getDateTimeByTimestamp($row["time"]);
			$date = DateUtil::format($dateTime, 'wcf.date.dateFormat');
			$time = DateUtil::format($dateTime, 'wcf.date.timeFormat');
			
			$message = new ConversationMessage($row["messageID"]);
			$quote = MessageQuoteManager::getInstance()->renderQuote($message, $row["message"], false);
			
			$this->preMessageContentFormatted .= "[quote='".$quote["username"]."','".$quote["link"]."'][size=8][i][color=#aaaaaa][sup]".$date." ".$time."[/sup][/color][/i][/size][block]".$quote["text"]."[/block][/quote]";
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'preConversationID' => $this->preConversationID,
			'subject' => $this->preSubject,
			'text' => $this->preMessageContentFormatted
		));
	}
}