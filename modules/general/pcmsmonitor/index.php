<?php

/**
 * Parses comma-separated relative paths into a clean list
 *
 * @param string $rawPaths
 *
 * @return array
 */
function pcmsmonitor_parse_paths($rawPaths) {
    $result = array();
    if ($rawPaths !== '' and $rawPaths !== null) {
        $parts = explode(',', $rawPaths);
        foreach ($parts as $io => $part) {
            $part = str_replace('\\', '/', trim($part));
            $part = trim($part, '/');
            if ($part !== '') {
                $result[] = $part;
            }
        }
    }
    return ($result);
}

/**
 * Checks whether a relative path is ignored (exact or under an ignored directory)
 *
 * @param string $relPath
 * @param array  $ignorePaths
 *
 * @return bool
 */
function pcmsmonitor_is_ignored($relPath, $ignorePaths) {
    $result = false;
    $relPath = str_replace('\\', '/', $relPath);
    $relPath = trim($relPath, '/');
    if ($relPath !== '' and !empty($ignorePaths)) {
        foreach ($ignorePaths as $io => $ignore) {
            if ($relPath === $ignore) {
                $result = true;
            } else {
                $prefix = $ignore . '/';
                if (strpos($relPath, $prefix) === 0) {
                    $result = true;
                }
            }
        }
    }
    return ($result);
}

/**
 * Checks whether remote IP is allowed for guest monitor access
 *
 * Empty allow-list means all IPs are allowed.
 *
 * @param string $allowedIpsRaw
 *
 * @return bool
 */
function pcmsmonitor_ip_allowed($allowedIpsRaw) {
    $result = true;
    $allowed = array();
    if ($allowedIpsRaw !== '' and $allowedIpsRaw !== null) {
        $parts = explode(',', $allowedIpsRaw);
        foreach ($parts as $io => $ip) {
            $ip = trim($ip);
            if ($ip !== '') {
                $allowed[] = $ip;
            }
        }
    }
    if (!empty($allowed)) {
        $remote = '';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remote = trim($_SERVER['REMOTE_ADDR']);
        }
        $matched = false;
        foreach ($allowed as $io => $ip) {
            if ($remote === $ip) {
                $matched = true;
            }
        }
        if (!$matched) {
            $result = false;
        }
    }
    return ($result);
}

/**
 * Recursively collects file modification times under a directory
 *
 * @param string $dirPath    absolute directory to scan
 * @param string $basePath   base path stripped from result keys
 * @param array  $ignorePaths relative paths to skip
 *
 * @return array
 */
function pcmsmonitor_collect_mtimes($dirPath, $basePath, $ignorePaths) {
    $result = array();
    $entries = @scandir($dirPath);
    if ($entries !== false) {
        foreach ($entries as $io => $name) {
            if ($name != '.' and $name != '..') {
                $fullPath = $dirPath . '/' . $name;
                $relPath = $fullPath;
                if (strpos($fullPath, $basePath) === 0) {
                    $relPath = substr($fullPath, strlen($basePath));
                    $relPath = ltrim(str_replace('\\', '/', $relPath), '/');
                }
                if (!pcmsmonitor_is_ignored($relPath, $ignorePaths)) {
                    if (!is_link($fullPath)) {
                        if (is_dir($fullPath)) {
                            $nested = pcmsmonitor_collect_mtimes($fullPath, $basePath, $ignorePaths);
                            if (!empty($nested)) {
                                foreach ($nested as $nestedPath => $mtime) {
                                    $result[$nestedPath] = $mtime;
                                }
                            }
                        } else {
                            if (is_file($fullPath)) {
                                $mtime = @filemtime($fullPath);
                                if ($mtime !== false) {
                                    $result[$relPath] = $mtime;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return ($result);
}

global $ubillingConfig;

$monitorEnabled = false;
$monitorIps = '';
$ignorePathsRaw = '';
if (isset($ubillingConfig) and is_object($ubillingConfig)) {
    if ($ubillingConfig->getAlterParam('PCMS_MONITOR_ENABLED')) {
        $monitorEnabled = true;
    }
    $altCfg = $ubillingConfig->getAlter();
    if (is_array($altCfg)) {
        if (isset($altCfg['PCMS_MONITOR_IPS']) and $altCfg['PCMS_MONITOR_IPS'] !== '') {
            $monitorIps = $altCfg['PCMS_MONITOR_IPS'];
        }
        if (isset($altCfg['PCMS_MONITOR_IGNORE_PATHS']) and $altCfg['PCMS_MONITOR_IGNORE_PATHS'] !== '') {
            $ignorePathsRaw = $altCfg['PCMS_MONITOR_IGNORE_PATHS'];
        }
    }
}

if ($monitorEnabled) {
    $showLatestTable = false;
    if ($system->getAuthEnabled()) {
        if (cfr('CMSMGR')) {
            $showLatestTable = true;
        }
    }

    $guestAllowed = false;
    if (!$showLatestTable) {
        if (pcmsmonitor_ip_allowed($monitorIps)) {
            $guestAllowed = true;
        }
    }

    if ($showLatestTable or $guestAllowed) {
        $ignorePaths = pcmsmonitor_parse_paths($ignorePathsRaw);
        $scanRoot = getcwd();
        $fileMtimes = array();
        if (!empty($scanRoot) and is_dir($scanRoot)) {
            $fileMtimes = pcmsmonitor_collect_mtimes($scanRoot, $scanRoot, $ignorePaths);
        }

        $latestMtime = 0;
        if (!empty($fileMtimes)) {
            $latestMtime = max($fileMtimes);
        }
        $latestDate = date('Y-m-d H:i:s', $latestMtime);

        if ($showLatestTable) {
            $scannedCount = sizeof($fileMtimes);

            $cells = wf_TableCell(__('File'));
            $cells .= wf_TableCell(__('Modified'));
            $rows = wf_TableRow($cells, 'row1');

            if (!empty($fileMtimes)) {
                arsort($fileMtimes);
                $topFiles = array_slice($fileMtimes, 0, 20, true);
                foreach ($topFiles as $relPath => $mtime) {
                    $cells = wf_TableCell($relPath);
                    $cells .= wf_TableCell(date('Y-m-d H:i:s', $mtime));
                    $rows .= wf_TableRow($cells, 'row5');
                }
            }

            $report = wf_TableBody($rows, '100%', 0, 'sortable');
            $report .= wf_tag('br');
            $report .= __('Scanned files') . ': ' . $scannedCount;
            show_window(__('Latest modified files'), $report);
        } else {
            die($latestDate);
        }
    } else {
        show_error(__('Access denied'));
    }
} else {
    show_error(__('This module is disabled'));
}
