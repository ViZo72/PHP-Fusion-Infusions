<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/functions.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

function get_docs_query(array $filters = []) {
    return "SELECT d.*, dc.*
        FROM ".DB_DOCS." AS d
        LEFT JOIN ".DB_DOCS_CATS." AS dc ON d.docs_cat=dc.docs_cat_id
        ".(multilang_table('DOC') ? "WHERE ".in_group('d.docs_language', LANGUAGE)." AND ".in_group('dc.docs_cat_language', LANGUAGE)." AND " : 'WHERE ')."
        ".(!empty($filters['condition']) ? ' '.$filters['condition'] : '')."
        GROUP BY d.docs_id
        ".(!empty($filters['order']) ? 'ORDER BY '.$filters['order'] : '')."
        ".(!empty($filters['limit']) ? 'LIMIT '.$filters['limit'] : '')."
    ";
}

function validate_docs($id) {
    if (isnum($id)) {
        if ($id < 1) {
            return 1;
        } else {
            return dbcount("('docs_id')", DB_DOCS, (multilang_column('DOC') ? in_group('docs_language', LANGUAGE)." AND " : '')."docs_id='".intval($id)."'");
        }
    }

    return FALSE;
}

function validate_docs_cat($id) {
    if (isnum($id)) {
        if ($id < 1) {
            return 1;
        } else {
            return dbcount("('docs_cat_id')", DB_DOCS_CATS, (multilang_column('DOC') ? in_group('docs_cat_language', LANGUAGE)." AND " : '')."docs_cat_id='".intval($id)."'");
        }
    }

    return FALSE;
}

function parse_docs_text($text) {
    $text = parse_textarea($text, TRUE, FALSE, TRUE, IMAGES_DOCS, TRUE);

    return $text;
}
