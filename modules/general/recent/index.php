<?php

$cms = new PseudoCMS();

if ($cms->isEnabled()) {
    if ($cms->dataPathsExist()) {
        $recentHtml = $cms->renderRecentArticles();
        if (!empty($recentHtml)) {
            $feedTitle = $cms->getArticlesModuleName();
            $cms->applyMutableTitle($feedTitle);
            show_window($feedTitle, $cms->renderCustomStylesLink() . $recentHtml);
        } else {
            show_window(__('Recent articles'), __('Set PCMS_RECENT_COUNT in alter.ini to show latest articles here.'));
        }
    } else {
        show_error($cms->getPublicStorageError());
    }
} else {
    show_error(__('PseudoCMS disabled'));
}
