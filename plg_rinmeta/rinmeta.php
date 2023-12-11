<?php
/**
 * Content Plugin for Joomla! - RinMeta
 *
 * @author     rinenweb.eu <info@rinenweb.eu>
 * @license    GNU GPL v3 or later
 * @link       https://github.com/rinenweb/plg_content_rinmeta
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class PlgContentRinmeta extends CMSPlugin
{
    /**
     * Event method onContentBeforeDisplay
     * @param   string  $context  The context of the content being passed to the plugin
     * @param   mixed   &$row     An object with a "text" property
     * @param   mixed   &$params  Additional parameters
     * @param   int     $page     Optional page number
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        $twitterAccount = $this->params->get('twitteraccount', '');
        $type = $this->params->get('type', 'summary');

        if ($context == 'com_content.article') {
            // Load Twitter meta tags
            $this->setTwitterMetadata(
                $this->setMetatitle($row->metadata, $row->title),
                $this->setImage($row->images, $row->text),
                $this->setMetadesc($row->metadesc, $row->text),
                $twitterAccount,
                $type
            );

            // Load Open Graph meta tags
            $this->setOpenGraphMetadata(
                $this->setMetatitle($row->metadata, $row->title),
                $this->setImage($row->images, $row->text),
                $this->setMetadesc($row->metadesc, $row->text),
                Uri::current()
            );
        }
    }

    /**
     * Set Twitter meta tags
     */
    protected function setTwitterMetadata($title, $image, $metadesc, $twitterAccount, $type)
    {
        $doc = Factory::getDocument();
        $doc->setMetaData('twitter:card', $type);
        $doc->setMetaData('twitter:site', $twitterAccount);
        $doc->setMetaData('twitter:title', $title);
        $doc->setMetaData('twitter:description', $metadesc);
        $doc->setMetaData('twitter:image', $image);
    }

    /**
     * Set Open Graph meta tags
     */
    protected function setOpenGraphMetadata($title, $image, $metadesc, $url)
    {
        $doc = Factory::getDocument();
        $config = Factory::getConfig();
    
        $doc->setMetaData('og:title', $title);
        $doc->setMetaData('og:description', $metadesc);
        $doc->setMetaData('og:image', $image);
        $doc->setMetaData('og:url', $url);
        $doc->setMetaData('og:type', 'article'); // You can adjust the type based on your content
        $doc->setMetaData('og:locale', $config->get('language'));
        $doc->setMetaData('og:site_name', $config->get('sitename'));

        // Fetch the Facebook App ID from plugin parameters
        $facebookAppId = $this->params->get('facebookappid', '');

        // Set the Facebook App ID meta tag if a value is provided
        if (!empty($facebookAppId)) {
            $doc->setMetaData('fb:app_id', $facebookAppId);
        }
    }

    /**
     * Extract article's image
     */
    protected function setImage($images, $text)
    {
        $fullImage = json_decode($images);
        preg_match_all('|<img.*?src=[\'"](.*?)[\'"].*?>|i', $text, $matches);

        if (!empty($fullImage->image_fulltext)) {
            $image = JUri::base() . $fullImage->image_fulltext;
        } elseif (!empty($matches[1][0])) {
            $image = $matches[1][0];
            $image = str_replace(JUri::base(), '', $image);
            $image = JUri::base() . $image;
        } else {
            $image = '';
        }

        return $image;
    }

    /**
     * Extract article's meta title
     */
    protected function setMetatitle($metadata, $title)
    {
        $metadata = json_decode($metadata);
        if (!empty($metadata->metatitle)) {
            $metatitle = $metadata->metatitle;
        } else {
            $metatitle = htmlspecialchars($title);
        }

        return $metatitle;
    }

/**
 * Extract article's meta description
 */
protected function setMetadesc($metadesc, $text, $limit = 159)
    {
    // If the meta description is provided, use it
    if ($metadesc) {
        return $metadesc;
    }

    // Otherwise, trim the text to the specified character limit without breaking words
    $text = strip_tags($text);
    if (strlen($text) > $limit) {
        $text = substr($text, 0, $limit);
        $lastSpace = strrpos($text, ' ');

        if ($lastSpace !== false) {
            $text = substr($text, 0, $lastSpace);
        }

        $text .= '...';
    }

    return $text;
    }
}
