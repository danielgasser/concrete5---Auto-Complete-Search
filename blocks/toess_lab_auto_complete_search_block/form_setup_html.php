<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * Realized with PhpStorm.
 * File: /packages/toess_lab_auto_complete_search/blocks/toess_lab_auto_complete_search_block/form_setup_html.php
 * Author: toesslab.ch
 * Date: 19.10.17
 * Time: 00:08
 */

/*
 * Verify object.
 */
$c = \Concrete\Core\Page\Page::getCurrentPage();
$tooltipTitle = t('Insert query placeholder');
$pageAttr = [];
foreach ($pageAttributes as $pa) {
    foreach ($pa as $k => $p) {
        $pageAttr[] = $k;
    }
}
if (!$controller->indexExists()) {
    ?>
    <div class="ccm-error"><?php echo  t('The search index does not appear to exist. This block will not function until the reindex job has been run at least once in the dashboard.') ?></div>
    <?php
}
  ?>

<fieldset>
    <div class="form-group">
        <?php echo  $form->label('title', t('Title')) ?>
        <?php echo  $form->text('title', $searchObj->title, ['maxlength' => 255]); ?>
    </div>
    <div class="form-group" id="pageAttribbutes">
        <label for="title" class="control-label"><?php echo t('Page attributes to show in the dropdown') ?>:</label>
        <div class="checkbox">
            <?php
            foreach ($attributes as $key => $attr) {
                ?>
                <label for="page_attr-<?php echo $key ?>">
                    <input id="page_attr-<?php echo $key ?>" name="page_attr-<?php echo $key ?>" type="checkbox" value="<?php echo $attr ?>" <?php echo (in_array($key, $pageAttr)) ? 'checked' : '' ?> />
                    <?php echo $attr ?>
                </label><br>
                <?php
            }
            ?>

        </div>
    </div>
    <div class="form-group">
        <label for="minLength" class="control-label"><?php echo t('Minimum search term length:') ?></label>
        <input type="number" class="form-control" id="minLength" name="minLength" value="<?php echo  ($minLength) ? $minLength : 3?>">
    </div>
    <div class="form-group">
        <label for="minLength" class="control-label"><?php echo t('Cut results after x characters:') ?></label>
        <input type="number" class="form-control" id="cutAfter" name="cutAfter" value="<?php echo  ($cutAfter) ? $cutAfter : 150?>">
    </div>
    <div class="form-group">
        <label for="maxResults" class="control-label"><?php echo t('Maximum results shown') ?></label>
        <input type="number" class="form-control" id="maxResults" name="maxResults" min="1" value="<?php echo  ($maxResults) ? $maxResults : 10?>">
    </div>
    <div class="form-group">
        <label for="noResultText" class="control-label"><?php echo t('No results text:') ?>&nbsp;<a id="toess_lab_auto_complete_search_tooltip" href="#" data-toggle="tooltip" data-html="true" title="<div style='text-align: left'><?php echo t('The button <b>%s</b> places a query placeholder inside this text at the cursors position: %s', $tooltipTitle, '<i>{{query}}</i>') . '<br>' . t('<b>Do not change the placeholder!</b><p>This placeholder will be replaced by the query text if no results are found.</p>') ?></div>">&nbsp;<i class="fa fa-question-circle" aria-hidden="true"></i></a></label>
            <button class="btn btn-block" id="toess_lab_auto_complete_search_query_place_holder"><?php echo $tooltipTitle ?></button>
        <input type="text" class="form-control" id="noResultText" name="noResultText" value="<?php echo htmlentities($noResultText)?>">
        <button class="btn btn-block" id="toess_lab_auto_complete_search_query_place_holder_delete"><?php echo t('Delete query placeholder') ?></button>
        <input type="hidden" id="queryPlaceholder" name="queryPlaceholder" value="<?php echo $queryPlaceholder ?>">
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="highlight" name="highlight" value="1" <?php echo  ($highlight == 1) ? 'checked' : ''?>>
            <?php echo  t('Highlight suggestions')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <input type="checkbox" id="showPath" name="showPath" value="1" <?php echo  ($showPath == 1) ? 'checked' : ''?>>
            <?php echo  t('Show link to result page')?>
        </label>
    </div>
    <div class="form-group">
        <label for="classNames" class="control-label"><?php echo  t('CSS class names')?></label><br>
        <?php
        $cNames = (is_array($classNames) && count($classNames) > 0) ? $classNames : $controller->defaultClassNames;

            foreach($cNames as $key => $cs) {
                if (strpos($key, 'page-attr-') !== false) {
                    $key = str_replace('page-attr-', '', $key);
                    $disabled = (in_array($key, $pageAttr)) ? '' : 'readonly="readonly"';
                    $class = ' page_attr-' . $key;
                } else {
                    $disabled = '';
                    $class = '';
                }
                echo $form->label($key, t(str_replace('_', ' ', $key)));
                echo "<input class='form-control$class' type='text' id='className[$key]' name='className[$key]' maxlength='255' $disabled value='$cs' >";
               // echo $form->text("className[$key]", $cs, ['maxlength' => 255, $disabled]);
            }
        ?>
    </div>
    <div class="form-group">
        <?php
        $baseSearchPage = null;
        $baseSearchPath = 'EVERYWHERE';
        if ((string) $controller->baseSearchPath !== '') {
            $baseSearchPage = Page::getByPath($controller->baseSearchPath);
            if (is_object($baseSearchPage) && !$baseSearchPage->isError()) {
                if (is_object($c) && $c->getCollectionID() == $baseSearchPage->getCollectionID()) {
                    $baseSearchPath = 'THIS';
                    $baseSearchPage = null;
                } else {
                    $baseSearchPath = 'OTHER';
                }
            } else {
                $baseSearchPage = null;
            }
        }
        ?>
        <?php echo  $form->label('', t('Search for Pages')) ?>
        <div class="radio">
            <label>
                <?php echo  $form->radio('baseSearchPath', 'EVERYWHERE', $baseSearchPath === 'EVERYWHERE') ?>
                <?php echo  t('Everywhere') ?>
            </label>
        </div>
        <div class="radio">
            <label>
                <?php echo  $form->radio('baseSearchPath', 'THIS', $baseSearchPath === 'THIS') ?>
                <?php echo  t('Beneath the Current Page') ?>
            </label>
        </div>
        <div class="radio">
            <label>
                <?php echo  $form->radio('baseSearchPath', 'OTHER', $baseSearchPath === 'OTHER') ?>
                <?php echo  t('Beneath Another Page') ?>
            </label>
        </div>
        <div class="ccm-searchBlock-baseSearchPath" data-for="OTHER" style="<?php echo  $baseSearchPath === 'OTHER' ? '' : 'display:none;' ?>">
            <?php echo  $pageSelector->selectPage('searchUnderCID', $baseSearchPath === 'OTHER' ? $baseSearchPage->getCollectionID() : null) ?>
        </div>
    </div>
</fieldset>
<script>
    var cursorPos,
        noDataPlaceholder = '<?php echo $controller->noResultTextQueryPlaceHolder ?>';
    document.getElementById("noResultText").addEventListener("click", function(){
    },false);
$(function() {
    $('input[name="baseSearchPath"]').on('change', function() {
        var value = $('input[name="baseSearchPath"]:checked').val();
        $('div.ccm-searchBlock-baseSearchPath')
            .hide()
            .filter('[data-for="' + value + '"]').show();
        ;
    }).trigger('change');
    $('[data-toggle="tooltip"]').tooltip();
    $(document).on('keyup click', '#noResultText', function (e) {
        e.preventDefault();
        var text = this;
        cursorPos = text.selectionStart;
        console.log('?')
    });
    $(document).on('click', '#toess_lab_auto_complete_search_tooltip', function (e) {
        e.preventDefault();
    });
    $(document).on('click', '#toess_lab_auto_complete_search_query_place_holder', function (e) {
        e.preventDefault();
        var noResultText = $('#noResultText'),
            text = noResultText.val(),
            cursor = (cursorPos !== undefined) ? cursorPos : text.length,
            newText;
        if (text.indexOf(noDataPlaceholder) > -1) {
            return false;
        }
        newText = [text.slice(0, cursor), noDataPlaceholder, text.slice(cursor)].join('');
        noResultText.val(newText);
        $('#queryPlaceholder').val(1);
    });
    $(document).on('click', '#toess_lab_auto_complete_search_query_place_holder_delete', function (e) {
        e.preventDefault();
        var noResultText = $('#noResultText'),
            text = noResultText.val(),
            pos = text.indexOf(noDataPlaceholder),
            newText;
        if (pos === -1) {
            return false;
        }
        newText = text.replace(noDataPlaceholder, '');
        noResultText.val(newText);
        $('#queryPlaceholder').val(0);
    });
    $(document).on ('change', '[id^="page_attr"]', function (e) {
        e.preventDefault();
        var el = $(this).attr('id');
        if ($(this).is(':checked')) {
            $('.' + el).attr('readonly', false);
        } else {
            $('.' + el).attr('readonly', true);
        }
    })
});
</script>
