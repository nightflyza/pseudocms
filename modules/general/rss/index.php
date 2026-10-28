<?php

$cms = new PseudoCMS();

if ($cms->isEnabled()) {
    if ($cms->isRssEnabled()) {
        if ($cms->dataPathsExist()) {
            $cms->flushRssFeed();
        } else {
            show_error($cms->getPublicStorageError());
        }
    } else {
        show_error(__('RSS disabled'));
    }
} else {
    show_error(__('PseudoCMS disabled'));
}
