<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Media;

use Exception;
use Pollen\Filesystem\LocalFilesystemInterface;
use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\StorageProxy;
use RuntimeException;
use WP_Error;
use WP_Query;

class Upload implements UploadInterface
{
    use StorageProxy;

    /**
     * Instance des paramètres de configuration.
     * @var ParamsBag|null
     */
    protected $params;

    /**
     * Instance du repertoire de destination.
     * @var LocalFilesystemInterface|null
     */
    protected $disk;

    /**
     * @inheritDoc
     */
    public function add(string $file, array $args = []): int
    {
        $finfo = wp_check_filetype($file);
        if (!$finfo['ext'] || !$finfo['type']) {
            throw new Exception('Impossible de définir le type de fichier ou le type n\'est pas autorisé.');
        } elseif (!$filemtime = filemtime($file)) {
            throw new Exception('Impossible de récupérer la date de modification du fichier.');
        } elseif (!$filesize = filesize($file)) {
            throw new Exception('Impossible de récupérer la taille du fichier.');
        } elseif (($maxSize = $this->getMaxSize()) && ($maxSize < $filesize)) {
            throw new Exception(
                sprintf(
                    'La taille du fichier dépasse le poids maximum de %s autorisé.',
                    size_format($maxSize)
                )
            );
        }

        $args = array_merge(
            [
                'attachment_id'  => 0,
                'guid'           => '',
                'name'           => '',
                'post_content'   => '',
                'post_excerpt'   => '',
                'post_mime_type' => '',
                'post_parent'    => 0,
                'post_title'     => '',
                'sanitize_name'  => true,
            ],
            $args
        );

        $name = $args['name'] ?: basename($file);
        $name = $args['sanitize_name'] ? sanitize_file_name($name) : $name;

        $exists = (new WP_Query())->query(
            [
                'fields'         => 'ids',
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'posts_per_page' => -1,
                'post_mime_type' => $finfo['type'],
                'meta_query'     => [
                    [
                        'key'     => '_wp_attached_file',
                        'value'   => $name,
                        'compare' => 'RLIKE',
                    ],
                ],
            ]
        );

        foreach ($exists as $id) {
            if (!$path = get_attached_file($id)) {
                continue;
            }

            if ($this->disk()->getAbsolutePath() . $name === $path) {
                $args['attachment_id'] = $id;
                break;
            }
        }

        if ($args['attachment_id'] && !$this->isRenewable() &&
            $this->disk()->fileExists($name) &&
            ($meta = wp_get_attachment_metadata($args['attachment_id']))
        ) {
            $mtime = $meta['filemtime'] ?? 0;
            $size = $meta['filesize'] ?? 0;

            if (($mtime == $filemtime) && ($size == $filesize)) {
                return $args['attachment_id'];
            }
        }

        try {
            $this->disk()->write($name, file_get_contents($file));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $file = $this->disk()->getAbsolutePath($name);

        $id = wp_insert_attachment(
            [
                'ID'             => $args['attachment_id'],
                'post_content'   => $args['post_content'],
                'post_excerpt'   => $args['post_excerpt'],
                'post_mime_type' => $this->disk()->mimeType($name),
                'post_parent'    => $args['post_parent'],
                'post_title'     => $args['post_title'] ?: sanitize_title($name),
            ],
            $file,
            0,
            true
        );

        if ($id instanceof WP_Error) {
            throw new Exception($id->get_error_message());
        } else {
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            wp_update_attachment_metadata(
                $id,
                array_merge(
                    wp_generate_attachment_metadata($id, $file),
                    compact('filemtime', 'filesize')
                )
            );
        }

        return $id;
    }

    /**
     * @inheritDoc
     */
    public function defaultParams(): array
    {
        return [
            'dest_dir' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getMaxSize(): int
    {
        return (int)$this->params('max_size', 0);
    }

    /**
     * @inheritDoc
     */
    public function getStorageDir(): ?string
    {
        if ($this->params('storage_dir')) {
            return (string)$this->params('storage_dir');
        }

        if ($dir = wp_upload_dir()) {
            if (!isset($dir['error']) || ($dir['error'] !== false)) {
                return null;
            }
            return $dir['path'] ?? null;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isRenewable(): bool
    {
        return $this->params('renewable', true);
    }

    /**
     * @inheritDoc
     */
    public function disk(): LocalFilesystemInterface
    {
        if ($this->disk === null) {
            $dir = $this->getStorageDir();

            if(!is_dir($dir) && !wp_mkdir_p($dir)) {
                throw new RuntimeException('Wordpress Upload disk unavailable');
            }

            $this->disk = $this->storage()->createLocalFilesystem($dir);
        }

        return $this->disk;
    }

    /**
     * @inheritDoc
     */
    public function params($key = null, $default = null)
    {
        if (!$this->params instanceof ParamsBag) {
            $this->params = new ParamsBag();
            $this->params->set($this->defaultParams());
        }

        if (is_string($key)) {
            return $this->params->get($key, $default);
        }

        if (is_array($key)) {
            $this->params->set($key);

            return $this->params;
        }
        return $this->params;
    }

    /**
     * @inheritDoc
     */
    public function setMaxSize(int $size): UploadInterface
    {
        $this->params(['max_size' => $size]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRenewable(bool $renew = true): UploadInterface
    {
        $this->params(['renewable' => $renew]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params): UploadInterface
    {
        $this->params($params);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setStorageDir(string $dir): UploadInterface
    {
        return $this->setParams(['storage_dir' => $dir]);
    }
}