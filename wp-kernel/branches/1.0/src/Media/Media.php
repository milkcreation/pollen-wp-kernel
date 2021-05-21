<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Media;

use Pollen\Support\Img;
use Pollen\Support\MimeTypes;

class Media implements MediaInterface
{
    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        MimeTypes::setAllowedMapping(
            function () {
                $mapping = [
                    'extensions' => [],
                    'mimes'      => [],
                ];

                foreach (get_allowed_mime_types() as $exts => $mimeType) {
                    $exts = explode('|', $exts);
                    if (!isset($mapping['extensions'][$mimeType])) {
                        $mapping['extensions'][$mimeType] = [];
                    }
                    foreach ($exts as $ext) {
                        if (!in_array($ext, $mapping['extensions'][$mimeType])) {
                            $mapping['extensions'][$mimeType][] = $ext;
                        }
                        if (!isset($mapping['mimes'][$ext])) {
                            $mapping['mimes'][$ext] = [];
                        }
                        $mapping['mimes'][$ext][] = $mimeType;
                    }
                }

                return $mapping;
            }
        );

        /**
         * @see wp_get_attachment_url()
         */
        add_filter(
            'wp_get_attachment_url',
            function ($url, $post_id) {
                if (!$metadata = get_post_meta($post_id, '_wp_attachment_metadata', true)) :
                    return $url;
                endif;
                if (!isset($metadata['upload_dir'])) :
                    return $url;
                endif;

                if ($file = get_post_meta($post_id, '_wp_attached_file', true)) :
                    $url = $metadata['upload_dir']['baseurl'] . "/$file";
                else :
                    $url = get_the_guid($post_id);
                endif;

                if (is_ssl() && !is_admin() && 'wp-login.php' !== $GLOBALS['pagenow']) :
                    $url = set_url_scheme($url);
                endif;

                return $url;
            },
            10,
            2
        );

        /**
         * @see get_attached_file()
         */
        add_filter(
            'get_attached_file',
            function ($file, $attachment_id) {
                if (!$metadata = get_post_meta($attachment_id, '_wp_attachment_metadata', true)) :
                    return $file;
                endif;
                if (!isset($metadata['upload_dir'])) :
                    return $file;
                endif;

                $file = get_post_meta($attachment_id, '_wp_attached_file', true);
                $file = "{$metadata['upload_dir']['basedir']}/{$file}";

                return $file;
            },
            10,
            2
        );

        /**
         * Calcul des sources images inclus dans l'attribut 'srcset'.
         * @see wp_calculate_image_srcset()
         */
        add_filter(
            'wp_calculate_image_srcset',
            function ($sources, $size_array, $image_src, $image_meta, $attachment_id) {
                if (!$metadata = \get_post_meta($attachment_id, '_wp_attachment_metadata', true)) :
                    return $sources;
                endif;
                if (!isset($metadata['upload_dir'])) :
                    return $sources;
                endif;

                foreach ($sources as &$attrs) :
                    $attrs['url'] = $metadata['upload_dir']['url'] . '/' . basename($attrs['url']);
                endforeach;

                return $sources;
            },
            10,
            5
        );
    }

    /**
     * Récupération de la source d'une image de la médiathèque au format base64.
     *
     * @param int $id Identifiant de qualification du média.
     *
     * @return string|null
     */
    public function getBase64Src(int $id): ?string
    {
        return ($filename = get_attached_file($id)) ? Img::getBase64Src($filename) : null;
    }

    /**
     * Récupération du chemin absolu vers un fichier de la médiathèque.
     *
     * @param string $path Chemin relatif depuis la racine du site.
     *
     * @return string|null
     */
    public function getSrcFilename(string $path): ?string
    {
        if (preg_match('/^' . preg_quote(site_url('/'), '/') . '/', $path)) {
            $filename = preg_replace('/^' . preg_quote(site_url('/'), '/') . '/', ABSPATH, $path);
        } elseif (preg_match('/^' . preg_quote(network_site_url('/'), '/') . '/', $path)) {
            $filename = preg_replace('/^' . preg_quote(network_site_url('/'), '/') . '/', ABSPATH, $path);
        } else {
            $filename = $path;
        }

        return file_exists($filename) ? $filename : null;
    }
}