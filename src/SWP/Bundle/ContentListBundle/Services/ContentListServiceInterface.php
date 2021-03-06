<?php

declare(strict_types=1);

/*
 * This file is part of the Superdesk Web Publisher Content List Bundle.
 *
 * Copyright 2016 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\ContentListBundle\Services;

use SWP\Bundle\ContentBundle\Model\ArticleInterface;
use SWP\Component\ContentList\Model\ContentListInterface;

interface ContentListServiceInterface
{
    /**
     * @param ContentListInterface $contentList
     * @param ArticleInterface     $article
     * @param null                 $position
     *
     * @return ContentListItemInterface
     */
    public function addArticleToContentList(ContentListInterface $contentList, ArticleInterface $article, $position = null);
}
