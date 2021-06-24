<?php

declare(strict_types=1);

namespace Pollen\WpKernel;

use League\Route\Http\Exception\HttpExceptionInterface as BaseHttpExceptionInterface;
use League\Route\Http\Exception\NotFoundException as BaseNotFoundException;
use Pollen\Http\Response;
use Pollen\Http\ResponseInterface;
use Pollen\Support\ProxyResolver;
use Pollen\Support\Str;
use Pollen\Routing\BaseViewController;
use Pollen\Routing\Exception\HttpExceptionInterface;
use Pollen\Routing\Exception\NotFoundException;
use Pollen\View\Engines\Plates\PlatesViewEngine;
use Pollen\View\ViewManager;
use Pollen\View\ViewManagerInterface;
use RuntimeException;

class WpFallbackController extends BaseViewController
{
    /**
     * Cartographie des méthodes de récupération des gabarits d'affichage
     * @see ./wp-includes/template-loader.php
     * @var array<string, string>
     */
    protected array $wpTemplateTags = [
        'is_embed'             => 'get_embed_template',
        'is_404'               => 'get_404_template',
        'is_search'            => 'get_search_template',
        'is_front_page'        => 'get_front_page_template',
        'is_home'              => 'get_home_template',
        'is_privacy_policy'    => 'get_privacy_policy_template',
        'is_post_type_archive' => 'get_post_type_archive_template',
        'is_tax'               => 'get_taxonomy_template',
        'is_attachment'        => 'get_attachment_template',
        'is_single'            => 'get_single_template',
        'is_page'              => 'get_page_template',
        'is_singular'          => 'get_singular_template',
        'is_category'          => 'get_category_template',
        'is_tag'               => 'get_tag_template',
        'is_author'            => 'get_author_template',
        'is_date'              => 'get_date_template',
        'is_archive'           => 'get_archive_template',
    ];

    public function __invoke(): ResponseInterface
    {
        $args = func_get_args();

        if (isset($args[0]) && $args[0] instanceof BaseHttpExceptionInterface) {
            return $this->exceptionRender(...$args);
        }

        return $this->dispatch(...$args);
    }

    /**
     * Répartiteur de requête HTTP.
     *
     * @return ResponseInterface
     */
    public function dispatch(): ResponseInterface
    {
        $args = func_get_args();

        foreach (array_keys($this->wpTemplateTags) as $tag) {
            if ($tag() && ($response = $this->handleTag($tag, ...$args))) {
                return $response;
            }
        }

        if (!$response = $this->handleTag('is_404', ...$args)) {
            $response = $this->response('Template unavailable', 404);
        }
        return $response;
    }

    /**
     * Affichage des exceptions.
     *
     * @param BaseHttpExceptionInterface|HttpExceptionInterface $e
     * @param array $args
     * @return ResponseInterface
     */
    public function exceptionRender(BaseHttpExceptionInterface $e, ...$args): ResponseInterface
    {
        if ($e instanceof NotFoundException || $e instanceof BaseNotFoundException) {
            return $this->dispatch(...$args);
        }

        ob_start();
        _default_wp_die_handler(
            $e->getMessage(),
            $e instanceof HttpExceptionInterface ? $e->getTitle() : get_class($e),
            [
                'exit' => false,
                'code' => $e->getStatusCode(),
            ]
        );
        $content = ob_get_clean();

        return new Response($content);
    }

    /**
     * Traitement de la requête HTTP.
     *
     * @param string $tag Indicateur de contexte d'affichage Wordpress.
     * @param array ...$args Liste des arguments dynamiques de requête HTTP
     *
     * @return ResponseInterface|null
     */
    public function handleTag(string $tag, ...$args): ?ResponseInterface
    {
        $method = Str::camel($tag);

        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }

        if ($template = call_user_func($this->wpTemplateTags[$tag])) {
            if ('attachment' === $tag) {
                remove_filter('the_content', 'prepend_attachment');
            }
        } else {
            $template = get_index_template();
        }

        if ($template = apply_filters('template_include', $template)) {
            $template = preg_replace(
                '#' . preg_quote(get_template_directory(), DIRECTORY_SEPARATOR) . '#',
                '',
                $template
            );

            try {
                $viewManager = ViewManager::getInstance();
            } catch (RuntimeException $e) {
                $viewManager = ProxyResolver::getInstance(
                    ViewManagerInterface::class,
                    ViewManager::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
            $view = $viewManager->createView('plates', ['extension' => 'php'])
                 ->setDirectory(get_template_directory());

            return $this->response($view->render(pathinfo($template, PATHINFO_FILENAME)));
        }
        return null;
    }
}