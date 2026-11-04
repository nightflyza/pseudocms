<?php

$cms = new PseudoCMS();

if ($cms->isEnabled()) {
    if ($cms->dataPathsExist()) {
        if (ubRouting::checkGet($cms::ROUTE_PAGEID)) {
            $pageId = ubRouting::get($cms::ROUTE_PAGEID, 'gigasafe');
            $pageId = $cms->filterPageId($pageId);
            if (!empty($pageId)) {
                $page = $cms->getPage($pageId);
                if (!empty($page)) {
                    if (!empty($page['title'])) {
                        $cms->applyMutableTitle($page['title']);
                    }
                    $windowTitle = $page['title'] . $cms->renderPageEditControl($pageId);
                    show_window($windowTitle, $cms->renderCustomStylesLink() . $cms->renderPage($pageId));
                } else {
                    show_error(__('Page not found'));
                }
            } else {
                show_error(__('Empty page ID'));
            }
        } else {
            show_error(__('Empty page ID'));
        }
    } else {
        show_error($cms->getPublicStorageError());
    }
} else {
    show_error(__('PseudoCMS disabled'));
}
