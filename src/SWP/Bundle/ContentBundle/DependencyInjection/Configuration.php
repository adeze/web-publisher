<?php

/*
 * This file is part of the Superdesk Web Publisher Content Bundle.
 *
 * Copyright 2016 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\ContentBundle\DependencyInjection;

use SWP\Bundle\ContentBundle\Doctrine\ORM\ArticleRepository;
use SWP\Bundle\ContentBundle\Doctrine\ORM\FileRepository;
use SWP\Bundle\ContentBundle\Doctrine\ORM\RouteRepository;
use SWP\Bundle\ContentBundle\Doctrine\ORM\ArticleMediaRepository;
use SWP\Bundle\ContentBundle\Doctrine\ORM\ImageRepository;
use SWP\Bundle\ContentBundle\Factory\ORM\ArticleFactory;
use SWP\Bundle\ContentBundle\Factory\ORM\MediaFactory;
use SWP\Bundle\ContentBundle\Factory\RouteFactory;
use SWP\Bundle\ContentBundle\Model\Article;
use SWP\Bundle\ContentBundle\Model\ArticleInterface;
use SWP\Bundle\ContentBundle\Model\ArticleMedia;
use SWP\Bundle\ContentBundle\Model\ArticleMediaInterface;
use SWP\Bundle\ContentBundle\Model\File;
use SWP\Bundle\ContentBundle\Model\FileInterface;
use SWP\Bundle\ContentBundle\Model\Image;
use SWP\Bundle\ContentBundle\Model\ImageInterface;
use SWP\Bundle\ContentBundle\Model\ImageRendition;
use SWP\Bundle\ContentBundle\Model\ImageRenditionInterface;
use SWP\Bundle\ContentBundle\Model\Route;
use SWP\Bundle\ContentBundle\Model\RouteInterface;
use SWP\Bundle\StorageBundle\Doctrine\ORM\EntityRepository;
use SWP\Component\Storage\Factory\Factory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('swp_content')
            ->children()
                ->arrayNode('persistence')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('orm')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('article')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->cannotBeEmpty()->defaultValue(Article::class)->end()
                                                ->scalarNode('interface')->cannotBeEmpty()->defaultValue(ArticleInterface::class)->end()
                                                ->scalarNode('repository')->defaultValue(ArticleRepository::class)->end()
                                                ->scalarNode('factory')->defaultValue(ArticleFactory::class)->end()
                                                ->scalarNode('object_manager_name')->defaultValue(null)->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('route')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->cannotBeEmpty()->defaultValue(Route::class)->end()
                                                ->scalarNode('interface')->cannotBeEmpty()->defaultValue(RouteInterface::class)->end()
                                                ->scalarNode('repository')->defaultValue(RouteRepository::class)->end()
                                                ->scalarNode('factory')->defaultValue(RouteFactory::class)->end()
                                                ->scalarNode('object_manager_name')->defaultValue(null)->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('media')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->cannotBeEmpty()->defaultValue(ArticleMedia::class)->end()
                                                ->scalarNode('interface')->cannotBeEmpty()->defaultValue(ArticleMediaInterface::class)->end()
                                                ->scalarNode('repository')->defaultValue(ArticleMediaRepository::class)->end()
                                                ->scalarNode('factory')->defaultValue(MediaFactory::class)->end()
                                                ->scalarNode('object_manager_name')->defaultValue(null)->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('image')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->cannotBeEmpty()->defaultValue(Image::class)->end()
                                                ->scalarNode('interface')->cannotBeEmpty()->defaultValue(ImageInterface::class)->end()
                                                ->scalarNode('repository')->defaultValue(ImageRepository::class)->end()
                                                ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                                ->scalarNode('object_manager_name')->defaultValue(null)->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('file')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->cannotBeEmpty()->defaultValue(File::class)->end()
                                                ->scalarNode('interface')->cannotBeEmpty()->defaultValue(FileInterface::class)->end()
                                                ->scalarNode('repository')->defaultValue(FileRepository::class)->end()
                                                ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                                ->scalarNode('object_manager_name')->defaultValue(null)->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('image_rendition')
                                            ->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('model')->cannotBeEmpty()->defaultValue(ImageRendition::class)->end()
                                                ->scalarNode('interface')->cannotBeEmpty()->defaultValue(ImageRenditionInterface::class)->end()
                                                ->scalarNode('repository')->defaultValue(EntityRepository::class)->end()
                                                ->scalarNode('factory')->defaultValue(Factory::class)->end()
                                                ->scalarNode('object_manager_name')->defaultValue(null)->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end() // classes
                            ->end()
                        ->end() // orm
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
