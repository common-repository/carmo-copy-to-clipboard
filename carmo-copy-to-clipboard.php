<?php
/*
Plugin Name: Carmo Copy to Clipboard
Plugin URI: https://www.carmo.pt/project/copy-to-clipboard/
Description: Adds a 'Copy to Clipboard' button to Gutenberg code blocks.
Version: 1.0.0
Author: carmo
Author URI: https://carmo.pt
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit;
}

$plugin_dir = plugin_dir_path(__FILE__);


if (!function_exists('carmoctc_addbutton')) {
    function carmoctc_addbutton($block_content, $block)
    {
        if ($block['blockName'] === 'core/code') {
            // Add the class 'has-copy-button' to the code block
            $block_content = str_replace(
                '<pre',
                '<pre class="has-copy-button language-javascript" style="position: relative;"',
                $block_content
            );
            // Add the copy button to the code block
            $button_html = '<button class="copy-button">Copy</button>';
            $block_content = str_replace(
                '</pre>',
                $button_html . '</pre>',
                $block_content
            );

            // Enqueue custom CSS file if has-copy-button class is present
            if (strpos($block_content, 'has-copy-button') !== false) {
                wp_enqueue_style('carmo-copy-to-clipboard-css', plugins_url('/css/carmo-copy-to-clipboard.css', __FILE__));
            }
        }
        return $block_content;
    }
    add_filter('render_block', 'carmoctc_addbutton', 10, 2);

}

if (!function_exists('carmoctc_load')) {
    function carmoctc_load()
    {
        // Check if there is at least one code block with the class 'wp-block-code'
        if (strpos(get_the_content(), 'wp-block-code') !== false) {
            // Enqueue Prism styles and scripts
            // PrismJS 1.29.0
            wp_enqueue_style('prism-css', plugins_url('/css/prism-okaidia.min.css', __FILE__));
            wp_enqueue_script('prism-js', plugins_url('/js/prism.min.js', __FILE__), array(), '1.29.0', true);

            // Add custom styles for code blocks
            $custom_css = "
         pre {
               overflow-x: auto;
               word-wrap: normal;
         }
      ";
            wp_add_inline_style('prism-css', $custom_css);

            // Enqueue ClipboardJS script
            // ClipboardJS 2.0.11
            //wp_enqueue_script('clipboard-js', plugins_url('/js/clipboard.min.js', __FILE__), array(), '2.0.11', true);
            
            wp_enqueue_script( 'clipboard', includes_url( '/js/clipboard.min.js' ), array(), true );
            
            ?>
            <script>
                //console.log('Copy to Clipboard plugin loaded');
                document.addEventListener('DOMContentLoaded', function () {

                    // Add custom class to each code block that needs syntax highlighting
                    var codeBlocks = document.querySelectorAll('.wp-block-code');
                    for (var i = 0; i < codeBlocks.length; i++) {
                        codeBlocks[i].classList.add('language-' + codeBlocks[i].getAttribute('data-language'));
                    }

                    new ClipboardJS('.copy-button', {
                        target: function (trigger) {
                            return trigger.parentElement.querySelector('code');
                        }
                    }).on('success', function (event) {
                        //console.log('Code block copied to clipboard');
                        event.trigger.innerHTML = 'Copied';
                        setTimeout(function () {
                            event.trigger.innerHTML = 'Copy';
                            if (window.getSelection) { window.getSelection().removeAllRanges(); }
                            else if (document.selection) { document.selection.empty(); }
                        }, 2000);
                    });
                });
            </script>
            <?php
        } else {
            // DEBUG
            // echo 'No code blocks with class "wp-block-code" found';
        }
    }
    add_action('wp_footer', 'carmoctc_load');
}
?>