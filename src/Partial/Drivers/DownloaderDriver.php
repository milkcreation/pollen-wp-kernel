<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers;

use Exception;
use Pollen\Partial\Drivers\DownloaderDriver as BaseDownloaderDriver;
use Pollen\Support\ParamsBag;
use Pollen\Support\Proxy\EncrypterProxy;
use Pollen\Validation\Validator as v;
use Pollen\Support\MimeTypes;

class DownloaderDriver extends BaseDownloaderDriver
{
    use EncrypterProxy;

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getFilename(...$args): string
    {
        if ($decrypt = $this->decrypt((string)$args[0])) {
            $var = (new ParamsBag())->set(json_decode(base64_decode($decrypt), true));
        } else {
            throw new Exception(
                'ERREUR SYSTEME : Impossible de récupérer les données de téléchargement du fichier.'
            );
        }

        $src = $var->get('src');
        if (is_numeric($src)) {
            $path = get_attached_file($src);
        } elseif (!is_string($src)) {
            throw new Exception(
                'Téléchargement impossible, la fichier source n\'est pas valide.'
            );
        } elseif (v::url()->validate(dirname($src))) {
            $path = Url::rel($src);
        } else {
            $path = $src;
        }

        if (file_exists($path)) {
            $filename = $path;
        } elseif (file_exists($var->get('basedir') . $path)) {
            $filename = $var->get('basedir') . $path;
        } else {
            throw new Exception(
                'Téléchargement impossible, le fichier n\'est pas disponible.'
            );
        }

        $types = $var->get('types');
        if (is_string($var->get('types'))) {
            $types = array_map('trim', explode(',', $var->get('types')));
        }

        if (!MimeTypes::inAllowedType($filename, $types)) {
            throw new Exception(
                'Téléchargement impossible, ce type de fichier n\'est pas autorisé.'
            );
        }

        return $filename;
    }
}