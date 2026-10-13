<?php
/*
 * Plugin Name: Altmetric embeds
 * Plugin URI: http://api.altmetric.com/embeds.html
 * Description: This plugin allows you to easily add altmetric embeds
 * Version: 0.0.9
 * Author: epikentros (fork of original by Altmetric)
 * Author URI: https://github.com/epikentros
 * License: GPL2
 *
 * Changelog:
 * 0.0.9 - Security hardening: every shortcode attribute is now sanitised and
 *         escaped before being written into the page (fixes stored XSS via
 *         shortcode attributes); enumerated attributes are checked against
 *         whitelists; inline styles go through safecss_filter_attr(); the embed
 *         script is enqueued once in the footer instead of being prepended to
 *         every the_content call (filter: altmetric_embeds_load_script).
 * 0.0.8 - Fixed PHP 8.x compatibility: non-static methods declared as static
 *         to prevent fatal errors when used as WordPress callbacks.
 *         Original plugin by Altmetric (deprecated).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Altmetric
{
    const EMBED_SCRIPT_URL = 'https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js';

    private static $badge_types = array('donut', 'medium-donut', 'large-donut', '1', '4');
    private static $popovers = array('left', 'right', 'top', 'bottom');
    private static $floats = array('left', 'right', 'none');

    /**
     * NOTE: This method of substituting text into divs is deprecated
     * (not hooked by default). Captured values are escaped before output.
     */
    public static function dois($content)
    {
        $new_content = preg_replace_callback(
            "/altmetric:(doi|arxiv-id|pmid|handle):(\S+):popover:(\S+):float:(\S+):altmetric/i",
            function ($m) {
                $type = strtolower($m[1]);
                $popover = in_array($m[3], self::$popovers, true) ? $m[3] : 'right';
                $float = in_array($m[4], self::$floats, true) ? $m[4] : 'none';
                return "<div class='altmetric-embed' data-badge-type='donut' "
                    . "data-" . $type . "='" . esc_attr($m[2]) . "' "
                    . "data-badge-popover='" . esc_attr($popover) . "' "
                    . "style='float: " . esc_attr($float) . "'></div>";
            },
            $content
        );
        if (PREG_NO_ERROR !== preg_last_error() || null === $new_content) {
            return $content;
        }
        return $new_content;
    }

    public static function altmetricCode($atts, $content, $tag)
    {
        $atts = (array) $atts;
        $a = shortcode_atts(array(
            'doi' => null,
            'arxiv_id' => null,
            'arxiv' => null,
            'pmid' => null,
            'handle' => null,
            'float' => null,
            'popover' => null,
            'class' => '',
            'style' => '',
            'details' => null,
            'example' => null,
            'type' => 'donut',
            'hide_no_mentions' => null,
        ), $atts, 'altmetric');

        $identifier = '';
        $ident_type = '';
        $candidates = array(
            array('doi', 'doi'),
            array('arxiv_id', 'arxiv-id'),
            array('arxiv', 'arxiv-id'),
            array('pmid', 'pmid'),
            array('handle', 'handle'),
        );
        foreach ($candidates as $candidate) {
            $value = isset($a[$candidate[0]]) ? sanitize_text_field($a[$candidate[0]]) : '';
            if ($value !== '') {
                $identifier = $value;
                $ident_type = $candidate[1];
                break;
            }
        }
        if ($identifier === '') {
            # no valid identifier...
            return "<!-- No identifier found, requires one of doi,pmid,arxiv_id,handle -->";
        }

        # class: keep only valid class tokens
        $classes = array('altmetric-embed');
        foreach (preg_split('/\s+/', trim((string) $a['class'])) as $c) {
            $c = sanitize_html_class($c);
            if ($c !== '') {
                $classes[] = $c;
            }
        }
        $embed_class = implode(' ', $classes);

        # type: whitelist, fall back to the default badge
        $type = in_array((string) $a['type'], self::$badge_types, true) ? (string) $a['type'] : 'donut';

        # style: float is whitelisted, free-form CSS goes through WordPress' CSS filter
        $style_parts = '';
        if ($a['float'] !== null && in_array((string) $a['float'], self::$floats, true)) {
            $style_parts .= 'float: ' . $a['float'] . '; ';
        }
        $style_parts .= (string) $a['style'];
        $style_parts = safecss_filter_attr($style_parts);
        $embed_style = $style_parts !== '' ? "style='" . esc_attr($style_parts) . "'" : '';

        $embed_popover = '';
        if ($a['popover'] !== null && in_array((string) $a['popover'], self::$popovers, true)) {
            $embed_popover = "data-badge-popover='" . esc_attr($a['popover']) . "'";
        }
        $embed_details = '';
        if ($a['details'] !== null && (string) $a['details'] === 'right') {
            $embed_details = "data-badge-details='right'";
        }
        $embed_no_mentions = '';
        if ($a['hide_no_mentions'] !== null) {
            $flag = ((string) $a['hide_no_mentions'] === 'false') ? 'false' : 'true';
            $embed_no_mentions = "data-hide-no-mentions='" . $flag . "'";
        }

        $embed_element = "<div class='" . esc_attr($embed_class) . "' data-badge-type='" . esc_attr($type) . "' "
            . "data-" . $ident_type . "='" . esc_attr($identifier) . "' {$embed_popover} {$embed_style} "
            . "{$embed_no_mentions} {$embed_details}></div>";

        if ($a['example'] !== null) {
            $shortcode_example = "<code>[" . esc_html($tag);
            foreach ($atts as $key => $value) {
                if ($key == 'example') {
                    continue;
                }
                $shortcode_example .= " " . esc_html((string) $key) . "=&quot;" . esc_html((string) $value) . "&quot;";
            }
            $shortcode_example .= "]</code>";
            if ($a['example'] == "before") {
                $embed_element .= $shortcode_example;
            } else {
                $embed_element = $shortcode_example . $embed_element;
            }
        }
        return $embed_element;
    }

    /**
     * Enqueue the Altmetric embed script once, in the footer.
     * Use add_filter('altmetric_embeds_load_script', '__return_false') to disable.
     */
    public static function enqueueScript()
    {
        if (!apply_filters('altmetric_embeds_load_script', true)) {
            return;
        }
        wp_enqueue_script('altmetric-embed', self::EMBED_SCRIPT_URL, array(), null, true);
    }
}

add_action('wp_enqueue_scripts', array('Altmetric', 'enqueueScript'));
add_shortcode('altmetric', array('Altmetric', 'altmetricCode'));
