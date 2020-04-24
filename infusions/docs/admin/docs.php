<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/admin/docs.php
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

$locale = fusion_get_locale();

$data = [
    'docs_id'       => 0,
    'docs_name'     => '',
    'docs_cat'      => 0,
    'docs_article'  => '',
    'docs_language' => LANGUAGE,
    'docs_hidden'   => []
];

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.fusion_get_aidlink());
}

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['docs_id']) && isnum($_GET['docs_id']))) {
    dbquery("DELETE FROM ".DB_DOCS." WHERE docs_id='".intval($_GET['docs_id'])."'");
    addNotice('success', $locale['docs_201']);
    redirect(DOCS.'admin.php'.fusion_get_aidlink());
}

if (isset($_POST['save_page']) || isset($_POST['save_and_close'])) {
    $data = [
        'docs_id'       => form_sanitizer($_POST['docs_id'], 0, 'docs_id'),
        'docs_name'     => form_sanitizer($_POST['docs_name'], '', 'docs_name'),
        'docs_cat'      => form_sanitizer($_POST['docs_cat'], 0, 'docs_cat'),
        'docs_article'  => form_sanitizer($_POST['docs_article'], '', 'docs_article'),
        'docs_language' => form_sanitizer($_POST['docs_language'], '', 'docs_language'),
        'docs_hidden'   => []
    ];

    if (dbcount("(docs_id)", DB_DOCS, "docs_id='".$data['docs_id']."'")) {
        if (\defender::safe()) {
            dbquery_insert(DB_DOCS, $data, 'update');
            addNotice('success', $locale['docs_202']);
        }
    } else {
        if (\defender::safe()) {
            dbquery_insert(DB_DOCS, $data, 'save');
            addNotice('success', $locale['docs_203']);
        }
    }

    if (isset($_POST['save_and_close'])) {
        redirect(clean_request('', ['ref', 'action', 'docs_id'], FALSE));
    } else {
        redirect(FUSION_REQUEST);
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['docs_id']) && isnum($_GET['docs_id']))) {
    $result = dbquery("SELECT * FROM ".DB_DOCS." ".(multilang_table('DOC') ? "WHERE ".in_group('docs_language', LANGUAGE)." AND" : "WHERE")." docs_id='".$_GET['docs_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);
        $data['docs_hidden'] = [$data['docs_id']];
    } else {
        redirect(clean_request('', ['section', 'aid'], TRUE));
    }
}

if (isset($_GET['ref']) && $_GET['ref'] == 'form') {
    echo openform('docsform', 'post', FUSION_REQUEST, ['enctype' => TRUE]);
    echo form_hidden('docs_id', '', $data['docs_id']);

    echo '<div class="row">';

    echo '<div class="col-xs-12 col-sm-8">';
    echo form_text('docs_name', $locale['docs_003'], $data['docs_name'], [
        'inline'     => TRUE,
        'required'   => TRUE,
        'error_text' => $locale['docs_100']
    ]);

    echo form_textarea('docs_article', $locale['docs_004'], $data['docs_article'], [
        'path'        => IMAGES_DOCS,
        'form_name'   => 'docsform',
        'type'        => 'html',
        'preview'     => TRUE,
        'height'      => '300px',
        'error_text'  => $locale['docs_101']
    ]);

    echo '</div>';

    echo '<div class="col-xs-12 col-sm-4">';
    openside();
    echo form_select_tree('docs_cat', $locale['docs_005'], $data['docs_cat'], [
        'required'     => TRUE,
        'parent_value' => $locale['choose'],
        'query'        => (multilang_table('DOC') ? "WHERE ".in_group('docs_cat_language', LANGUAGE) : ''),
        'inline'       => TRUE,
        'error_text'   => $locale['docs_102']
    ], DB_DOCS_CATS, 'docs_cat_name', 'docs_cat_id', 'docs_cat_parent');

    if (multilang_table('DOC')) {
        echo form_select('docs_language[]', $locale['global_ML100'], $data['docs_language'], [
            'options'     => fusion_get_enabled_languages(),
            'placeholder' => $locale['choose'],
            'inline'      => TRUE,
            'multiple'    => TRUE
        ]);
    } else {
        echo form_hidden('docs_language', '', $data['docs_language']);
    }

    closeside();
    echo '</div>';

    echo '</div>'; // .row

    echo form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-fw fa-times']);
    echo form_button('save_page', $locale['save'], $locale['save'], ['class' => 'btn-sm btn-success m-l-5', 'icon' => 'fa fa-fw fa-hdd-o']);
    echo form_button('save_and_close', $locale['save_and_close'], $locale['save_and_close'], ['class' => 'btn-sm btn-primary m-l-5', 'icon' => 'fa fa-floppy-o']);
    echo closeform();
} else {
    $allowed_actions = array_flip(['delete', 'display']);

    if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
        $input = (isset($_POST['docs_id'])) ? explode(',', form_sanitizer($_POST['docs_id'], '', 'docs_id')) : '';

        if (!empty($input)) {
            foreach ($input as $docs_id) {
                if (dbcount("('docs_id')", DB_DOCS, "docs_id=:docs_id", [':docs_id' => intval($docs_id)]) && \defender::safe()) {
                    switch ($_POST['table_action']) {
                        case 'delete':
                            dbquery("DELETE FROM ".DB_DOCS." WHERE docs_id=:docs_id", [':docs_id' => intval($docs_id)]);
                            break;
                        default:
                            redirect(FUSION_REQUEST);
                    }
                }
            }

            addNotice('success', $locale['docs_202']);
            redirect(FUSION_REQUEST);
        }

        addNotice('warning', $locale['docs_204']);
        redirect(FUSION_REQUEST);
    }

    if (isset($_POST['docs_clear'])) {
        redirect(FUSION_SELF.fusion_get_aidlink());
    }

    $sql_condition = multilang_table('DOC') ? in_group('docs_language', LANGUAGE) : '';
    $search_string = [];
    if (isset($_POST['p-submit-docs_text'])) {
        $search_string['docs_name'] = [
            'input'    => form_sanitizer($_POST['docs_text'], '', 'docs_text'),
            'operator' => 'LIKE'
        ];
    }

    if (!empty($_POST['docs_category'])) {
        $search_string['docs_cat'] = [
            'input'    => form_sanitizer($_POST['docs_category'], '', 'docs_category'),
            'operator' => '='
        ];
    }

    if (!empty($_POST['docs_language'])) {
        $search_string['docs_language'] = [
            'input'    => form_sanitizer($_POST['docs_language'], '', 'docs_language'),
            'operator' => '='
        ];
    }

    if (!empty($search_string)) {
        foreach ($search_string as $key => $values) {
            if ($sql_condition) {
                $sql_condition .= " AND ";
            }
            $sql_condition .= "`$key` ".$values['operator'].($values['operator'] == "LIKE" ? "'%" : "'").$values['input'].($values['operator'] == "LIKE" ? "%'" : "'");
        }
    }

    $default_display = 16;
    $limit = $default_display;
    if ((!empty($_POST['docs_display']) && isnum($_POST['docs_display'])) || (!empty($_GET['docs_display']) && isnum($_GET['docs_display']))) {
        $limit = (!empty($_POST['docs_display']) ? $_POST['docs_display'] : $_GET['docs_display']);
    }

    $max_rows = dbcount("(docs_id)", DB_DOCS);
    $rowstart = 0;
    if (!isset($_POST['docs_display'])) {
        $rowstart = (isset($_GET['rowstart']) && isnum($_GET['rowstart']) && $_GET['rowstart'] <= $max_rows ? $_GET['rowstart'] : 0);
    }

    $result = dbquery("SELECT d.*, dc.*
        FROM ".DB_DOCS." d
        LEFT JOIN ".DB_DOCS_CATS." AS dc ON d.docs_cat=dc.docs_cat_id
        ".($sql_condition ? " WHERE ".$sql_condition : "")."
        GROUP BY d.docs_id
        LIMIT $rowstart, $limit
    ");

    $docs_rows = dbrows($result);

    $filter_values = [
        'docs_text'     => !empty($_POST['docs_text']) ? form_sanitizer($_POST['docs_text'], '', 'docs_text') : '',
        'docs_category' => !empty($_POST['docs_category']) ? form_sanitizer($_POST['docs_category'], '', 'docs_category') : '',
        'docs_language' => !empty($_POST['docs_language']) ? form_sanitizer($_POST['docs_language'], '', 'docs_language') : ''
    ];

    $filter_empty = TRUE;
    foreach ($filter_values as $val) {
        if ($val) {
            $filter_empty = FALSE;
        }
    }

    echo '<div class="m-t-15">';
    echo openform('docs_filter', 'post', FUSION_REQUEST);
    echo '<div class="clearfix">';
        echo '<div class="pull-right">';
            echo '<a class="btn btn-success btn-sm" href="'.clean_request('ref=form', ['ref'], FALSE).'"><i class="fa fa-fw fa-plus"></i> '.$locale['docs_008'].'</a>';
            echo '<button type="button" class="hidden-xs btn btn-danger btn-sm m-l-5" onclick="run_admin(\'delete\', \'#table_action\', \'#docs_table\');"><i class="fa fa-fw fa-trash-o"></i> '.$locale['delete'].'</button>';
        echo '</div>';

        echo '<div class="display-inline-block pull-left m-r-10">';
            echo form_text('docs_text', '', $filter_values['docs_text'], [
                'placeholder'       => $locale['search'],
                'append_button'     => TRUE,
                'append_value'      => "<i class='fa fa-fw fa-search'></i>",
                'append_form_value' => 'search_docs',
                'width'             => '160px',
                'group_size'        => 'sm'
            ]);
        echo '</div>';

        echo '<div class="display-inline-block hidden-xs">';
            echo '<a class="btn btn-sm m-r-5 '.(!$filter_empty ? 'btn-info' : 'btn-default').'" id="toggle_options" href="#">'.$locale['search'].' <span id="filter_caret" class="fa '.(!$filter_empty ? 'fa-caret-up' : 'fa-caret-down').'"></span></a>';
            echo form_button('docs_clear', $locale['docs_009'], 'clear', ['class' => 'btn-default btn-sm']);
        echo '</div>';
    echo '</div>';

    echo '<div id="docs_filter_options"'.($filter_empty ? ' style="display: none;"' : '').'>';
        echo '<div class="display-inline-block">';
            echo form_select_tree('docs_category', '', $filter_values['docs_category'], [
                'parent_value' => $locale['docs_010'],
                'placeholder'  => '- '.$locale['docs_011'].' -',
                'allowclear'   => TRUE,
                'query'        => (multilang_table('DOC') ? "WHERE ".in_group('docs_cat_language', LANGUAGE) : '')
            ], DB_DOCS_CATS, 'docs_cat_name', 'docs_cat_id', 'docs_cat_parent');
        echo '</div>';

        echo '<div class="display-inline-block">';
            $language_opts = [0 => $locale['docs_012']];
            $language_opts += fusion_get_enabled_languages();
            echo form_select('docs_language', '', $filter_values['docs_language'], [
                'allowclear'  => TRUE,
                'placeholder' => '- '.$locale['docs_013'].' -',
                'options'     => $language_opts
            ]);
        echo '</div>';
    echo '</div>';
    echo closeform();
    echo '</div>';

    echo openform('docs_table', 'post', FUSION_REQUEST);
    echo form_hidden('table_action', '', '');

    echo '<div class="table-responsive"><table class="table table-hover">';
        echo '<thead><tr>';
            echo '<th class="hidden-xs"></th>';
            echo '<th>'.$locale['docs_003'].'</th>';
            echo '<th>'.$locale['docs_005'].'</th>';
            echo '<th>'.$locale['language'].'</th>';
            echo '<th>'.$locale['docs_014'].'</th>';
        echo '</tr></thead>';
        echo '<tbody>';

    if (dbrows($result) > 0) {
        while ($data = dbarray($result)) {
            $edit_link = clean_request('&ref=form&action=edit&docs_id='.$data['docs_id'], ['ref', 'action', 'docs_id'], FALSE);
            $delete_link = clean_request('&ref=form&action=delete&docs_id='.$data['docs_id'], ['ref', 'action', 'docs_id'], FALSE);

            echo '<tr data-id="'.$data['docs_id'].'" id="page'.$data['docs_id'].'">';
                echo '<td class="hidden-xs">';
                echo form_checkbox('docs_id[]', '', '', ['value' => $data['docs_id'], 'input_id' => 'checkbox'.$data['docs_id'], 'class' => 'm-b-0']);
                add_to_jquery('$("#checkbox'.$data['docs_id'].'").click(function() {
                    if ($(this).prop("checked")) {
                        $("#page'.$data['docs_id'].'").addClass("active");
                    } else {
                        $("#page'.$data['docs_id'].'").removeClass("active");
                    }
                });');
                echo '</td>';
                echo '<td>'.$data['docs_name'].'</td>';
                echo '<td>'.$data['docs_cat_name'].'</td>';
                echo '<td>'.translate_lang_names($data['docs_language']).'</td>';
                echo '<td>';
                    echo '<a href="'.$edit_link.'" title="'.$locale['edit'].'">'.$locale['edit'].'</a> | ';
                    echo '<a href="'.$delete_link.'" title="'.$locale['delete'].'">'.$locale['delete'].'</a>';
                echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="9" class="text-center">'.$locale['docs_016'].'</td></tr>';
    }
    echo '</tbody>';
    echo '</table></div>';

    echo '<div class="display-block">';
        echo '<label class="control-label display-inline-block m-r-10" for="docs_display">'.$locale['docs_017'].'</label>';
        echo '<div class="display-inline-block">';
            echo form_select('docs_display', '', $limit, ['options' => [5 => 5, 10 => 10, 16 => 16, 25 => 25, 50 => 50, 100 => 100]]);
        echo '</div>';

    if ($max_rows > $docs_rows) {
        echo '<div class="display-inline-block pull-right">';
            echo makepagenav($rowstart, $limit, $max_rows, 3, FUSION_SELF.fusion_get_aidlink()."&docs_display=$limit&amp;");
        echo '</div>';
    }
    echo '</div>';

    echo closeform();

    add_to_jquery("
        $('#toggle_options').bind('click', function(e) {
            e.preventDefault();
            $('#docs_filter_options').slideToggle();
            var caret_status = $('#filter_caret').hasClass('fa-caret-down');
            if (caret_status == 1) {
                $('#filter_caret').removeClass('fa-caret-down').addClass('fa-caret-up');
                $(this).removeClass('btn-default').addClass('btn-info');
            } else {
                $('#filter_caret').removeClass('fa-caret-up').addClass('fa-caret-down');
                $(this).removeClass('btn-info').addClass('btn-default');
            }
        });
        $('#docs_category, #docs_language, #docs_display').bind('change', function(e) {
            $(this).closest('form').submit();
        });
    ");
}
