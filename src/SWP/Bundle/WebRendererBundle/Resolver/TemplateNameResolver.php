<?php

/**
 * This file is part of the Superdesk Web Publisher Web Renderer Bundle.
 *
 * Copyright 2016 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * Some parts of that file were taken from the Liip/ThemeBundle
 * (c) Liip AG
 *
 * @copyright 2016 Sourcefabric z.ú.
 * @license http://www.superdesk.org/license
 */
namespace SWP\Bundle\WebRendererBundle\Resolver;

use SWP\Bundle\ContentBundle\Model\RouteInterface;
use SWP\Bundle\ContentBundle\Model\ArticleInterface;

class TemplateNameResolver implements TemplateNameResolverInterface
{
    public function resolveFromArticle(ArticleInterface $article, $default = 'article.html.twig')
    {
        $templateName = $default;
        if (null !== ($route = $article->getRoute())) {
            $routeTemplateName = $this->resolveFromRoute($route, false);
            if ($routeTemplateName) {
                $templateName = $routeTemplateName;
            }
        }

        if (null !== ($articleTemplateName = $article->getTemplateName())) {
            return $articleTemplateName;
        }

        return $templateName;
    }

    public function resolveFromRoute(RouteInterface $route, $default = 'article.html.twig') {
        if (null !== ($templateName = $route->getTemplateName())) {
            return $templateName;
        }

        return $templateName;
    }
}
