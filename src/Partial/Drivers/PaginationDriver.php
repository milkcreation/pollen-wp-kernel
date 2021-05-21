<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers;

class PaginationDriver
{
    /**
     * @inheritDoc
     */
    public function parseQuery(): BasePaginationDriverInterface
    {
        $this->query = $this->pull('query');
        if (!$this->query instanceof PaginationQueryInterface) {
            $this->query = new PaginationQuery($this->query);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function parseUrl(): BasePaginationDriverInterface
    {
        if ($this->has('url.base')) {
            $this->query->setBaseUrl($this->get('url.base', null));
        }

        if ($this->has('url.segment')) {
            $this->query->setSegmentUrl($this->get('url.segment'));
        } else {
            $this->query->setSegmentUrl(true);
        }

        if ($this->has('url.index')) {
            $this->query->setPageIndex($this->get('url.index'));
        }

        if (!is_array($this->get('url'))) {
            $this->query->setBaseUrl($this->get('url', null));
        }

        return $this;
    }
}