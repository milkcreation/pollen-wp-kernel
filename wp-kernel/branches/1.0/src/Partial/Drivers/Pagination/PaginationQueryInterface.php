<?php

declare(strict_types=1);

namespace Pollen\WpKernel\Partial\Drivers\Pagination;

use Pollen\Partial\Drivers\Pagination\PaginationQueryInterface as BasePaginationQueryInterface;
use WP_Query;

interface PaginationQueryInterface extends BasePaginationQueryInterface
{
    /**
     * Traitement de la requête WP_Query.
     *
     * @param WP_Query $wp_query
     *
     * @return array
     */
    public function wpQueryArgs(WP_Query $wp_query): array;
}