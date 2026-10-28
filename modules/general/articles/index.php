<?php

$cms = new PseudoCMS();

if ($cms->isEnabled()) {
    if ($cms->dataPathsExist()) {
        if (ubRouting::checkGet($cms::ROUTE_ARTICLE)) {
            $articleId = ubRouting::get($cms::ROUTE_ARTICLE, 'int');
            $articleId = $cms->filterArticleId($articleId);
            if (!empty($articleId)) {
                $article = $cms->getArticle($articleId);
                if (!empty($article)) {
                    $cms->applyMutableTitle($article['title']);
                    show_window($article['title'], $cms->renderCustomStylesLink() . $cms->renderArticle($articleId));
                } else {
                    show_error(__('Article not found'));
                }
            } else {
                show_error(__('Empty article ID'));
            }
        } else {
            $pageNum = 1;
            if (ubRouting::checkGet($cms::ROUTE_PAGE)) {
                $pageNum = ubRouting::get($cms::ROUTE_PAGE, 'int');
                if (empty($pageNum)) {
                    $pageNum = 1;
                }
            }
            $feedTitle = $cms->getArticlesModuleName();
            $cms->applyMutableTitle($feedTitle);
            show_window($feedTitle, $cms->renderCustomStylesLink() . $cms->renderArticlesFeed($pageNum));
        }
    } else {
        show_error($cms->getPublicStorageError());
    }
} else {
    show_error(__('PseudoCMS disabled'));
}
