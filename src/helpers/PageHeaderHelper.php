<?php

/**
 * PageHeaderHelper pro správu page headers a breadcrumbs
 * File: src/helpers/PageHeaderHelper.php
 */

namespace Helpers;

class PageHeaderHelper
{
    /**
     * Nastavení page header
     */
    public static function setPageHeader(&$data, $title_key, $custom_breadcrumbs = null, $background_image = null, $options = [])
    {
        $language = lang();

        // Překlad title
        if (is_array($title_key)) {
            $current_lang = $language->getCurrentLanguage();
            $title = $title_key[$current_lang] ?? $title_key['cs'] ?? reset($title_key);
        } else {
            $title = $language->translate($title_key, [], 'general');
        }

        // Breadcrumbs
        if ($custom_breadcrumbs) {
            $breadcrumbs = self::createCustomBreadcrumbs($custom_breadcrumbs);
        } else {
            $breadcrumbs = BreadcrumbHelper::auto();
        }

        $data['page_header'] = [
            'show' => true,
            'title' => $title,
            'breadcrumbs' => $breadcrumbs,
            'background_image' => $background_image,
            'options' => $options,
        ];

        return $data;
    }

    /**
     * Vlastní breadcrumbs
     */
    private static function createCustomBreadcrumbs($items)
    {
        $breadcrumbs = BreadcrumbHelper::manual();

        foreach ($items as $item) {
            $title_key = $item['title_key'] ?? $item['title'];
            $url = $item['url'] ?? null;
            $breadcrumbs->add($title_key, $url);
        }

        return $breadcrumbs;
    }
}
