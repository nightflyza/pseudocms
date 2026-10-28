<?php

if ($system->getAuthEnabled()) {
    if (cfr('CMSMGR')) {
        $cms = new PseudoCMS();
        if ($cms->isEnabled()) {
            $cms->ensurePaths();

            if ($cms->isStorageReady()) {
                // page delete
                if (ubRouting::checkGet($cms::ROUTE_DELETE_PAGE)) {
                    $deleteResult = '';
                    $deleteId = $cms->filterPageId(ubRouting::get($cms::ROUTE_DELETE_PAGE, 'gigasafe'));
                    if (!empty($deleteId)) {
                        $deleteResult = $cms->deletePage($deleteId);
                    }
                    if (!empty($deleteResult)) {
                        show_error($deleteResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_PAGES);
                    }
                }

                // element delete
                if (ubRouting::checkGet($cms::ROUTE_DELETE_ELEMENT)) {
                    $deleteResult = '';
                    $deleteId = $cms->filterElementId(ubRouting::get($cms::ROUTE_DELETE_ELEMENT, 'gigasafe'));
                    if (!empty($deleteId)) {
                        $deleteResult = $cms->deleteElement($deleteId);
                    }
                    if (!empty($deleteResult)) {
                        show_error($deleteResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ELEMENTS);
                    }
                }

                // article delete
                if (ubRouting::checkGet($cms::ROUTE_DELETE_ARTICLE)) {
                    $deleteResult = '';
                    $deleteId = $cms->filterArticleId(ubRouting::get($cms::ROUTE_DELETE_ARTICLE, 'int'));
                    if (!empty($deleteId)) {
                        $deleteResult = $cms->deleteArticle($deleteId);
                    }
                    if (!empty($deleteResult)) {
                        show_error($deleteResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ARTICLES);
                    }
                }

                // draft delete
                if (ubRouting::checkGet($cms::ROUTE_DELETE_DRAFT)) {
                    $deleteResult = '';
                    $deleteId = $cms->filterArticleId(ubRouting::get($cms::ROUTE_DELETE_DRAFT, 'int'));
                    if (!empty($deleteId)) {
                        $deleteResult = $cms->deleteDraft($deleteId);
                    }
                    if (!empty($deleteResult)) {
                        show_error($deleteResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_DRAFTS);
                    }
                }

                // move article to draft
                if (ubRouting::checkGet($cms::ROUTE_TO_DRAFT)) {
                    $moveResult = '';
                    $moveId = $cms->filterArticleId(ubRouting::get($cms::ROUTE_TO_DRAFT, 'int'));
                    if (!empty($moveId)) {
                        $moveResult = $cms->moveArticleToDraft($moveId);
                    }
                    if (!empty($moveResult)) {
                        show_error($moveResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_DRAFTS);
                    }
                }

                // publish draft to articles
                if (ubRouting::checkGet($cms::ROUTE_PUBLISH_DRAFT)) {
                    $moveResult = '';
                    $moveId = $cms->filterArticleId(ubRouting::get($cms::ROUTE_PUBLISH_DRAFT, 'int'));
                    if (!empty($moveId)) {
                        $moveResult = $cms->moveDraftToArticle($moveId);
                    }
                    if (!empty($moveResult)) {
                        show_error($moveResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ARTICLES);
                    }
                }

                // media download (raw file, skip skin)
                if (ubRouting::checkGet($cms::ROUTE_DOWNLOAD_MEDIA)) {
                    $cms->downloadMedia(ubRouting::get($cms::ROUTE_DOWNLOAD_MEDIA, 'raw'));
                }

                // media delete
                if (ubRouting::checkGet($cms::ROUTE_DELETE_MEDIA)) {
                    $deleteResult = '';
                    if ($cms->isMediaEnabled()) {
                        $deleteResult = $cms->deleteMedia(ubRouting::get($cms::ROUTE_DELETE_MEDIA, 'raw'));
                    } else {
                        $deleteResult = __('Media uploads disabled');
                    }
                    if (!empty($deleteResult)) {
                        show_error($deleteResult);
                    } else {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_MEDIA);
                    }
                }

                // media upload
                if (ubRouting::checkPost($cms::PROUTE_DOUPLOAD_MEDIA)) {
                    $uploadResult = $cms->uploadMediaFromRequest();
                    if (empty($uploadResult)) {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_MEDIA);
                    } else {
                        show_error($uploadResult);
                    }
                }

                // page save
                if (ubRouting::checkPost($cms::PROUTE_DOSAVE_PAGE)) {
                    $saveResult = $cms->savePageFromRequest();
                    if (empty($saveResult)) {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_PAGES);
                    } else {
                        show_error($saveResult);
                    }
                }

                // element save
                if (ubRouting::checkPost($cms::PROUTE_DOSAVE_ELEMENT)) {
                    $saveResult = $cms->saveElementFromRequest();
                    if (empty($saveResult)) {
                        ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ELEMENTS);
                    } else {
                        show_error($saveResult);
                    }
                }

                // article / draft save
                if (ubRouting::checkPost($cms::PROUTE_DOSAVE_ARTICLE)) {
                    $saveResult = $cms->saveArticleFromRequest();
                    if (empty($saveResult)) {
                        if ($cms->isDraftSaveRequest()) {
                            ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_DRAFTS);
                        } else {
                            ubRouting::nav($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ARTICLES);
                        }
                    } else {
                        show_error($saveResult);
                    }
                }

                $section = '';
                if (ubRouting::checkGet($cms::ROUTE_SECTION)) {
                    $section = ubRouting::get($cms::ROUTE_SECTION, 'gigasafe');
                }
                if (empty($section)) {
                    $section = $cms::SECTION_ARTICLES;
                }

                $cms->applyMutableTitle(__('CMS manager'));
                show_window('', $cms->renderMgrMenu($section));

                if ($section == $cms::SECTION_ARTICLES) {
                    if (ubRouting::checkGet($cms::ROUTE_NEW_ARTICLE)) {
                        show_window(__('Create article'), $cms->renderArticleForm());
                        show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ARTICLES));
                    } else {
                        if (ubRouting::checkGet($cms::ROUTE_EDIT_ARTICLE)) {
                            $editId = $cms->filterArticleId(ubRouting::get($cms::ROUTE_EDIT_ARTICLE, 'int'));
                            show_window(__('Edit article'), $cms->renderArticleForm($editId, false));
                            show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ARTICLES));
                        } else {
                            $pageNum = 1;
                            if (ubRouting::checkGet($cms::ROUTE_PAGE)) {
                                $pageNum = ubRouting::get($cms::ROUTE_PAGE, 'int');
                                if (empty($pageNum)) {
                                    $pageNum = 1;
                                }
                            }
                            show_window(__('Articles'), $cms->renderArticlesList($pageNum));
                        }
                    }
                } else {
                    if ($section == $cms::SECTION_DRAFTS) {
                        if (ubRouting::checkGet($cms::ROUTE_NEW_DRAFT)) {
                            show_window(__('Create draft'), $cms->renderArticleForm('', true));
                            show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_DRAFTS));
                        } else {
                            if (ubRouting::checkGet($cms::ROUTE_EDIT_DRAFT)) {
                                $editId = $cms->filterArticleId(ubRouting::get($cms::ROUTE_EDIT_DRAFT, 'int'));
                                show_window(__('Edit draft'), $cms->renderArticleForm($editId, true));
                                show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_DRAFTS));
                            } else {
                                $pageNum = 1;
                                if (ubRouting::checkGet($cms::ROUTE_PAGE)) {
                                    $pageNum = ubRouting::get($cms::ROUTE_PAGE, 'int');
                                    if (empty($pageNum)) {
                                        $pageNum = 1;
                                    }
                                }
                                show_window(__('Drafts'), $cms->renderDraftsList($pageNum));
                            }
                        }
                    } else {
                        if ($section == $cms::SECTION_PAGES) {
                            if (ubRouting::checkGet($cms::ROUTE_NEW_PAGE)) {
                                show_window(__('Create page'), $cms->renderPageForm());
                                show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_PAGES));
                            } else {
                                if (ubRouting::checkGet($cms::ROUTE_EDIT_PAGE)) {
                                    $editId = $cms->filterPageId(ubRouting::get($cms::ROUTE_EDIT_PAGE, 'gigasafe'));
                                    show_window(__('Edit page'), $cms->renderPageForm($editId));
                                    show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_PAGES));
                                } else {
                                    show_window(__('Pages'), $cms->renderPagesList());
                                }
                            }
                        } else {
                            if ($section == $cms::SECTION_ELEMENTS) {
                                if (ubRouting::checkGet($cms::ROUTE_NEW_ELEMENT)) {
                                    show_window(__('Create element'), $cms->renderElementForm());
                                    show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ELEMENTS));
                                } else {
                                    if (ubRouting::checkGet($cms::ROUTE_EDIT_ELEMENT)) {
                                        $editId = $cms->filterElementId(ubRouting::get($cms::ROUTE_EDIT_ELEMENT, 'gigasafe'));
                                        show_window(__('Edit element'), $cms->renderElementForm($editId));
                                        show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_ELEMENTS));
                                    } else {
                                        show_window(__('Elements'), $cms->renderElementsList());
                                    }
                                }
                            } else {
                                if ($section == $cms::SECTION_MEDIA) {
                                    if ($cms->isMediaEnabled()) {
                                        $mediaPathError = $cms->ensureMediaPath();
                                        if (!empty($mediaPathError)) {
                                            show_error($mediaPathError);
                                        } else {
                                            show_window(__('Media'), $cms->renderMediaPanel());
                                        }
                                    } else {
                                        show_error(__('Media uploads disabled'));
                                    }
                                } else {
                                    if (ubRouting::checkGet($cms::ROUTE_NEW_PAGE)) {
                                        show_window(__('Create page'), $cms->renderPageForm());
                                        show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_PAGES));
                                    } else {
                                        if (ubRouting::checkGet($cms::ROUTE_EDIT_PAGE)) {
                                            $editId = $cms->filterPageId(ubRouting::get($cms::ROUTE_EDIT_PAGE, 'gigasafe'));
                                            show_window(__('Edit page'), $cms->renderPageForm($editId));
                                            show_window('', wf_BackLink($cms::URL_ME . '&' . $cms::ROUTE_SECTION . '=' . $cms::SECTION_PAGES));
                                        } else {
                                            show_window(__('Pages'), $cms->renderPagesList());
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                show_error($cms->getStorageError());
            }
        } else {
            show_error(__('PseudoCMS disabled'));
        }
    } else {
        show_error(__('Access denied'));
    }
} else {
    show_error(__('Authorization engine disabled'));
}
