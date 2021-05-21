<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers;

use Pollen\Partial\Drivers\ImageLightboxDriver as BaseImageLightboxDriver;
use Pollen\Partial\Drivers\ImageLightbox\ImageLightboxItem;
use Pollen\Partial\Drivers\ImageLightbox\ImageLightboxItemInterface;
use Pollen\Validation\Validator as v;

class ImageLightboxDriver extends BaseImageLightboxDriver
{
    /**
     * @inheritDoc
     */
    public function fetchItem($item): ?ImageLightboxItemInterface
    {
        if ($item instanceof ImageLightboxItemInterface) {
            return $item;
        }

        if (is_array($item)) {
            if (isset($item['src']) && ($instance = $this->fetchItem($item['src']))) {
                unset($item['src']);
                return $instance->set($item);
            }
            if (isset($item['content'])) {
                $imageLightbox = new ImageLightboxItem();
                $imageLightbox->set($item);

                return $imageLightbox;
            }
            return null;
        }

        if (is_numeric($item) && ($src = wp_get_attachment_url($item))) {
            $imageLightbox = new ImageLightboxItem();
            $imageLightbox->set(
                [
                    'src' => $src,
                ]
            );

            return $imageLightbox;
        }

        if (is_string($item) && v::url()->validate($item)) {
            $imageLightbox = new ImageLightboxItem();
            $imageLightbox->set(
                [
                    'src' => $item,
                ]
            );

            return $imageLightbox;
        }

        return null;
    }
}