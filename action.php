<?php

/**
 * DokuWiki Plugin preview (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */
class action_plugin_preview extends \dokuwiki\Extension\ActionPlugin
{

    /** @inheritDoc */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'handleConfig');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handlePreview');
    }

    /**
     * Event handler for DOKUWIKI_STARTED
     *
     * @see https://www.dokuwiki.org/devel:events:DOKUWIKI_STARTED
     * @param Doku_Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleConfig(Doku_Event $event, $param) {
        global $JSINFO;
        $JSINFO['plugin']['preview'] = [
            'selector' => $this->getConf('selector'),
        ];
    }


    /**
     * Event handler for AJAX_CALL_UNKNOWN
     *
     * @see https://www.dokuwiki.org/devel:events:AJAX_CALL_UNKNOWN
     * @param Doku_Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handlePreview(Doku_Event $event, $param)
    {
        if ($event->data != 'plugin_preview') return;
        $event->preventDefault();
        $event->stopPropagation();

        global $INPUT;

        $id = $INPUT->str('id');
        if (!$id) http_status(404, 'No ID given');
        if (!page_exists($id)) http_status(404, 'Page does not exist');
        if (!auth_quickaclcheck($id) >= AUTH_READ) http_status(403, 'Access denied');

        $title = trim(p_get_first_heading($id));
        if ($title == '') http_status(404, 'Page has no title, probably not important');
        $abstract = p_get_metadata($id, 'description abstract');
        $image = p_get_metadata($id, 'relation firstimage');

        $abstract = substr($abstract, strlen($title)); // remove title from abstract
        $abstract = trim($abstract, '.…') . '…'; // always have ellipsis

        header('Content-Type: text/html; charset=utf-8');
        echo '<h2>' . hsc($title) . '</h2>';
        echo '<p>' . hsc($abstract) . '</p>';
        if ($image) echo '<img src="' . ml($image, ['w' => 400]) . '" alt="" />';
    }

}

