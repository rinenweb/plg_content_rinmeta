<?php
/**
 * Content Plugin for Joomla! - RinMeta
 *
 * @author     rinenweb.eu <info@rinenweb.eu>
 * @license    GNU GPL v3 or later
 * @link       https://github.com/rinenweb/plg_content_rinmeta
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Class PlgContentTweetcards
 *
 * @since  0.0.1
 */
class PlgContentTweetcards extends JPlugin
{
    /**
     * Event method onContentBeforeDisplay
     *
     * @param   string  $context  The context of the content being passed to the plugin
     * @param   mixed   &$row     An object with a "text" property
     * @param   mixed   &$params  Additional parameters
     * @param   int     $page     Optional page number
     *
     * @return  null
     *
     * @since 0.0.1
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {

        $twitteraccount     = $this->params->get('twitteraccount', '@htmgarcia');
        $type               = $this->params->get('type', 'summary');

        if ($context == 'com_content.article') {

            // Load twitter meta tags
            $this->setMetadata (
                $this->setMetatitle(
                    $row->metadata,
                    $row->title
                ),
                $this->setImage(
                    $row->images,
                    $row->text
                ),
                $this->setMetadesc(
                    $row->metadesc,
                    $row->text
                ),
                $twitteraccount,
                $type
            );

        }
    }

    /**
     * Event method that create the open graph meta tags
     *
     * @param   string  $title  The article's title
     *
     * @since 0.0.1
     */
    protected function setMetadata ($title, $image, $metadesc, $twitteraccount, $type) {
        $doc = JFactory::getDocument();
        $doc->setMetaData( 'twitter:card', $type );
        $doc->setMetaData( 'twitter:site', $twitteraccount );
        $doc->setMetaData( 'twitter:title', $title );
        $doc->setMetaData( 'twitter:description', $metadesc );
        $doc->setMetaData( 'twitter:image', $image );
    }

    /**
     * Extract article's image
     *
     * @return string
     *
     * @since 0.0.1
     */
    protected function setImage($images, $text) {
        $fullImage = json_decode($images);
        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $text, $matches);
        if ( !empty($fullImage->image_fulltext) ) {
            $image = JURI::base() . $fullImage->image_fulltext;
        } elseif( !empty($matches[ 1 ][ 0 ]) ) {
            $image = $matches[ 1 ][ 0 ];
            $image = str_replace(JURI::base(), '', $image);
            $image = JURI::base() . $image;
        } else {
            $image = '';
        }

        return $image;
    }

    /**
     * Extract article's meta description
     *
     * @return string
     *
     * @since 0.0.1
     */
    protected function setMetadesc($metadesc, $text) {
        if($metadesc) {
            $metadesc = $metadesc;
        } else {
            $metadesc = substr( strip_tags($text), 0, 157 ) . '...';
        }

        return $metadesc;
    }

    /**
     * Extract article's meta title
     *
     * @return string
     *
     * @since 0.0.1
     */
    protected function setMetatitle($metadata, $title) {
        $metadata = json_decode($metadata);
        if( !empty($metadata->metatitle) ) {
            $metatitle = $metadata->metatitle;
        } else {
            $metatitle = htmlspecialchars($title);
        }

        return $metatitle;
    }
}
