<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: docs/admin/docs_cats.php
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
    'docs_cat_id'          => 0,
    'docs_cat_name'        => '',
    'docs_cat_parent'      => 0,
    'docs_cat_description' => '',
    'docs_cat_language'    => LANGUAGE,
    'docs_cat_hidden'      => []
];

if (isset($_POST['cancel'])) {
    redirect(FUSION_SELF.fusion_get_aidlink().'&section=categories');
}

if ((isset($_GET['action']) && $_GET['action'] == 'delete') && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    if (dbcount("(docs_cat)", DB_DOCS, "docs_cat='".intval($_GET['cat_id'])."'")
        || dbcount("(docs_cat_id)", DB_DOCS_CATS, "docs_cat_parent='".intval($_GET['cat_id'])."'")
    ) {
        addNotice('danger', $locale['docs_205']);
        redirect(FUSION_SELF.fusion_get_aidlink().'&section=categories');
    } else {
        dbquery("DELETE FROM ".DB_DOCS_CATS." WHERE docs_cat_id='".intval($_GET['cat_id'])."'");
        addNotice('success', $locale['docs_206']);
        redirect(FUSION_SELF.fusion_get_aidlink().'&section=categories');
    }
}

if (isset($_POST['save_cat']) || isset($_POST['save_and_close'])) {
    $data = [
        'docs_cat_id'          => form_sanitizer($_POST['docs_cat_id'], 0, 'docs_cat_id'),
        'docs_cat_parent'      => form_sanitizer($_POST['docs_cat_parent'], 0, 'docs_cat_parent'),
        'docs_cat_name'        => form_sanitizer($_POST['docs_cat_name'], '', 'docs_cat_name'),
        'docs_cat_description' => form_sanitizer($_POST['docs_cat_description'], '', 'docs_cat_description'),
        'docs_cat_language'    => form_sanitizer($_POST['docs_cat_language'], '', 'docs_cat_language'),
        'docs_cat_hidden'      => []
    ];

    $category_name_check = [
        'when_updating' => "docs_cat_name='".$data['docs_cat_name']."' AND docs_cat_id !='".$data['docs_cat_id']."'",
        'when_saving'   => "docs_cat_name='".$data['docs_cat_name']."'",
    ];

    if (dbcount("(docs_cat_id)", DB_DOCS_CATS, "docs_cat_id='".$data['docs_cat_id']."'")) {
        if (!dbcount("(docs_cat_id)", DB_DOCS_CATS, $category_name_check['when_updating'])) {
            if (\defender::safe()) {
                dbquery_insert(DB_DOCS_CATS, $data, 'update');
                addNotice('success', $locale['docs_207']);
            }
        } else {
            \defender::stop();
            addNotice('danger', $locale['docs_208']);
        }
    } else {
        if (!dbcount("(docs_cat_id)", DB_DOCS_CATS, $category_name_check['when_saving'])) {
            if (\defender::safe()) {
                dbquery_insert(DB_DOCS_CATS, $data, 'save');
                addNotice('success', $locale['docs_209']);
            }
        } else {
            \defender::stop();
            addNotice('danger', $locale['docs_209']);
        }
    }

    if (isset($_POST['save_and_close'])) {
        redirect(clean_request('', ['ref', 'action', 'cat_id'], FALSE));
    } else {
        redirect(FUSION_REQUEST);
    }
}

if ((isset($_GET['action']) && $_GET['action'] == 'edit') && (isset($_GET['cat_id']) && isnum($_GET['cat_id']))) {
    $result = dbquery("SELECT * FROM ".DB_DOCS_CATS." ".(multilang_table('DOC') ? "WHERE ".in_group('docs_cat_language', LANGUAGE)." AND" : "WHERE")." docs_cat_id='".$_GET['cat_id']."'");

    if (dbrows($result)) {
        $data = dbarray($result);
        $data['docs_cat_hidden'] = [$data['docs_cat_id']];
    } else {
        redirect(FUSION_REQUEST);
    }
}

if (isset($_GET['ref']) && $_GET['ref'] == 'docs_cat_form') {
    echo openform('docs_cats', 'post', FUSION_REQUEST);
    echo form_hidden('docs_cat_id', '', $data['docs_cat_id']);

    echo '<div class="row">';
    echo '<div class="col-xs-12 col-sm-8">';
    echo form_text('docs_cat_name', $locale['docs_003'], $data['docs_cat_name'], [
        'required'   => TRUE,
        'inline'     => TRUE,
        'error_text' => $locale['docs_103']
    ]);
    echo form_select_tree('docs_cat_parent', $locale['docs_006'], $data['docs_cat_parent'], [
        'disable_opts'  => $data['docs_cat_hidden'],
        'hide_disabled' => TRUE,
        'width'         => '100%',
        'inline'        => TRUE
    ], DB_DOCS_CATS, 'docs_cat_name', 'docs_cat_id', 'docs_cat_parent');
    echo form_textarea('docs_cat_description', $locale['docs_018'], $data['docs_cat_description'], [
        'required' => TRUE,
        'resize'   => 0,
        'autosize' => TRUE,
        'type'     => 'bbcode'
    ]);
    echo '</div>';

    echo '<div class="col-xs-12 col-sm-4">';
    openside();
    if (multilang_table('DOC')) {
        echo form_select('docs_cat_language[]', $locale['global_ML100'], $data['docs_cat_language'], [
            'options'     => fusion_get_enabled_languages(),
            'placeholder' => $locale['choose'],
            'inline'      => TRUE,
            'width'       => '100%',
            'multiple'    => TRUE
        ]);
    } else {
        echo form_hidden('docs_cat_language', '', $data['docs_cat_language']);
    }

    closeside();
    echo '</div>';

    echo '</div>';

    echo form_button('cancel', $locale['cancel'], $locale['cancel'], ['class' => 'btn-sm btn-default', 'icon' => 'fa fa-fw fa-times']);
    echo form_button('save_cat', $locale['save'], $locale['save'], ['class' => 'btn-sm btn-success m-l-5', 'icon' => 'fa fa-fw fa-hdd-o']);
    echo form_button('save_and_close', $locale['save_and_close'], $locale['save_and_close'], ['class' => 'btn-sm btn-primary m-l-5', 'icon' => 'fa fa-floppy-o']);
    echo closeform();
} else {
    $allowed_actions = array_flip(['delete']);

    if (isset($_POST['table_action']) && isset($allowed_actions[$_POST['table_action']])) {
        $input = !empty($_POST['docs_cat_id']) ? form_sanitizer($_POST['docs_cat_id'], '', 'docs_cat_id') : '';

        if (!empty($input)) {
            $input = ($input ? explode(',', $input) : []);
            foreach ($input as $docs_cat_id) {
                if (dbcount("('docs_cat_id')", DB_DOCS_CATS, "docs_cat_id=:docs_cat", [':docs_cat' => intval($docs_cat_id)]) && \defender::safe()) {
                    switch ($_POST['table_action']) {
                        case 'delete':
                            if (!dbcount("(docs_id)", DB_DOCS, "docs_cat=:docs_cat", [':docs_cat' => $docs_cat_id]) && !dbcount("(docs_cat_id)", DB_DOCS_CATS, "docs_cat_parent=:catparent", [':catparent' => $docs_cat_id])) {
                                dbquery("DELETE FROM  ".DB_DOCS_CATS." WHERE docs_cat_id=:docs_cat_id", [':docs_cat_id' => intval($docs_cat_id)]);
                            } else {
                                addNotice('warning', $locale['docs_210']);
                                addNotice('warning', $locale['docs_211']);
                            }
                            break;
                        default:
                            redirect(clean_request('', ['action', 'ref'], FALSE));
                    }
                }
            }

            addNotice('success', $locale['docs_207']);
            redirect(FUSION_REQUEST);
        } else {
            addNotice('warning', $locale['docs_212']);
            redirect(FUSION_REQUEST);
        }
    }

    if (isset($_POST['docs_clear'])) {
        redirect(FUSION_SELF.fusion_get_aidlink()."&amp;section=categories");
    }

    $sql_condition = multilang_table('DOC') ? in_group('dc.docs_cat_language', LANGUAGE) : '';
    $search_string = [];
    if (isset($_POST['p-submit-docs_cat_name'])) {
        $search_string['docs_cat_name'] = [
            'input'    => form_sanitizer($_POST['docs_cat_name'], '', 'docs_cat_name'),
            'operator' => 'LIKE'
        ];
    }

    if (!empty($_POST['docs_cat_language'])) {
        $search_string['docs_cat_language'] = [
            'input'    => form_sanitizer($_POST['docs_cat_language'], '', 'docs_cat_language'),
            'operator' => "="
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

    $result = dbquery_tree_full(DB_DOCS_CATS, 'docs_cat_id', 'docs_cat_parent', '', "
        SELECT dc.*, COUNT(d.docs_id) AS docs_count
        FROM ".DB_DOCS_CATS." dc
        LEFT JOIN ".DB_DOCS." AS d ON d.docs_cat=dc.docs_cat_id
        ".($sql_condition ? " WHERE ".$sql_condition : "")."
        GROUP BY dc.docs_cat_id
    ");

    $filter_values = [
        'docs_cat_name'     => !empty($_POST['docs_cat_name']) ? form_sanitizer($_POST['docs_cat_name'], '', 'docs_cat_name') : '',
        'docs_cat_language' => !empty($_POST['docs_cat_language']) ? form_sanitizer($_POST['docs_cat_language'], '', 'docs_cat_language') : ''
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
                echo '<a class="btn btn-success btn-sm" href="'.clean_request('ref=docs_cat_form', ['ref'], FALSE).'"><i class="fa fa-fw fa-plus"></i> '.$locale['docs_019'].'</a>';
                echo '<button type="button" class="hidden-xs btn btn-danger btn-sm m-l-5" onclick="run_admin(\'delete\', \'#table_action\', \'#docs_table\');"><i class="fa fa-fw fa-trash-o"></i> '.$locale['delete'].'</button>';
            echo '</div>';

            echo '<div class="display-inline-block pull-left m-r-10">';
                echo form_text('docs_cat_name', '', $filter_values['docs_cat_name'], [
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
                $language_opts = [0 => $locale['docs_012']];
                $language_opts += fusion_get_enabled_languages();
                echo form_select('docs_cat_language', '', $filter_values['docs_cat_language'], [
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
    display_docs_category($result);
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
        $('#docs_cat_language').bind('change', function(e) {
            $(this).closest('form').submit();
        });
    ");
}

function display_docs_category($data, $id = 0, $level = 0) {
    $locale = fusion_get_locale();

    if (!$id) {
        echo '<div class="table-responsive"><table class="table table-hover">';
        echo '<thead><tr>';
            echo '<th class="hidden-xs"></th>';
            echo '<th class="col-xs-4">'.$locale['docs_003'].'</th>';
            echo '<th>'.$locale['docs_020'].'</th>';
            echo '<th>'.$locale['language'].'</th>';
            echo '<th>'.$locale['docs_014'].'</th>';
        echo '</tr></thead>';
        echo '<tbody>';
    }

    if (!empty($data[$id])) {
        foreach ($data[$id] as $cat_id => $cdata) {
            $edit_link = clean_request('section=categories&ref=docs_cat_form&action=edit&cat_id='.$cat_id, ['section', 'ref', 'action', 'cat_id'], FALSE);

            echo '<tr data-id="'.$cat_id.'" id="cat'.$cat_id.'">';
                echo '<td class="hidden-xs">';
                    echo form_checkbox('docs_cat_id[]', '', '', ['value' => $cat_id, 'input_id' => 'checkbox'.$cat_id, 'class' => 'm-b-0']);
                    add_to_jquery('$("#checkbox'.$cat_id.'").click(function() {
                        if ($(this).prop("checked")) {
                            $("#cat'.$cat_id.'").addClass("active");
                        } else {
                            $("#cat'.$cat_id.'").removeClass("active");
                        }
                    });');
                echo '</td>';

                echo '<td>'.str_repeat('|-', $level).' '.$cdata['docs_cat_name'].'</td>';
                echo '<td><span class="badge">'.$cdata['docs_count'].'</span></td>';
                echo '<td>'.translate_lang_names($cdata['docs_cat_language']).'</td>';
                echo '<td>';
                    echo '<a href="'.$edit_link.'" title="'.$locale['edit'].'">'.$locale['edit'].'</a> | ';
                    echo '<a href="'.FUSION_SELF.fusion_get_aidlink().'&section=categories&ref=docs_cat_form&action=delete&cat_id='.$cat_id.'" title="'.$locale['delete'].'">'.$locale['delete'].'</a>';
                echo '</td>';
            echo '</tr>';

            if (isset($data[$cdata['docs_cat_id']])) {
                display_docs_category($data, $cdata['docs_cat_id'], $level + 1);
            }
        }
    } else {
        echo '<tr><td colspan="6" class="text-center">'.$locale['docs_021'].'</td></tr>';
    }

    if (!$id) {
        echo '</tbody>';
        echo '</table></div>';
    }
}
