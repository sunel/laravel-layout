<?php

namespace Layout\Page;

class Messages extends \Layout\Block
{
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const SUCCESS = 'success';

    /**
     * Messages collection.
     *
     * @var Mage_Core_Model_Message_Collection
     */
    protected $_messages;
    /**
     * Store first level html tag name for messages html output.
     *
     * @var string
     */
    protected $_messagesFirstLevelTagName = 'ul';
    /**
     * Store second level html tag name for messages html output.
     *
     * @var string
     */
    protected $_messagesSecondLevelTagName = 'li';
    /**
     * Store content wrapper html tag name for messages html output.
     *
     * @var string
     */
    protected $_messagesContentWrapperTagName = 'span';

    /**
     * Retrieve messages in HTML format.
     *
     * @param string $type
     *
     * @return string
     */
    public function getHtml($type = null)
    {
        $html = '<'.$this->_messagesFirstLevelTagName.' id="admin_messages">';
        foreach ($this->getMessages($type) as $message) {
            $html .= '<'.$this->_messagesSecondLevelTagName.' class="'.$message->getType().'-msg">'
                .($this->_escapeMessageFlag) ? $this->escapeHtml($message->getText()) : $message->getText()
                .'</'.$this->_messagesSecondLevelTagName.'>';
        }
        $html .= '</'.$this->_messagesFirstLevelTagName.'>';

        return $html;
    }
    /**
     * Retrieve messages in HTML format grouped by type.
     *
     * @param string $type
     *
     * @return string
     */
    public function getGroupedHtml()
    {
        $types = [
            self::ERROR,
            self::WARNING,
            self::NOTICE,
            self::SUCCESS,
        ];
        $html = '';
        foreach ($types as $type) {
            if ($messages = $this->getMessages($type)) {
                if (!$html) {
                    $html .= '<'.$this->_messagesFirstLevelTagName.' class="messages">';
                }
                $html .= '<'.$this->_messagesSecondLevelTagName.' class="'.$type.'-msg">';
                $html .= '<'.$this->_messagesFirstLevelTagName.'>';
                foreach ($messages as $message) {
                    $html .= '<'.$this->_messagesSecondLevelTagName.'>';
                    $html .= '<'.$this->_messagesContentWrapperTagName.'>';
                    $html .= ($this->_escapeMessageFlag) ? $this->escapeHtml($message->getText()) : $message->getText();
                    $html .= '</'.$this->_messagesContentWrapperTagName.'>';
                    $html .= '</'.$this->_messagesSecondLevelTagName.'>';
                }
                $html .= '</'.$this->_messagesFirstLevelTagName.'>';
                $html .= '</'.$this->_messagesSecondLevelTagName.'>';
            }
        }
        if ($html) {
            $html .= '</'.$this->_messagesFirstLevelTagName.'>';
        }

        return $html;
    }
    protected function _toHtml()
    {
        return $this->getGroupedHtml();
    }
}
