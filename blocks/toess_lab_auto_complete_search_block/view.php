<?php defined('C5_EXECUTE') or die('Access Denied.');
/**
 * Realized with PhpStorm.
 * File: /packages/toess_lab_auto_complete_search/blocks/toess_lab_auto_complete_search_block/view.php
 * Author: toesslab.ch
 * Date: 19.10.17
 * Time: 00:08
 */
$parent = \Concrete\Core\Page\Page::getByID(1);
$parentLink = $parent->getCollectionLink();
$uniqueBID = $controller->getBlockUID($b);
$searchPath = $request->request('search_paths');
?>
<script>
    var toess_lab_auto_complete_search = {
            url_get_words: '<?php echo $view->action('get_results') ?>',
            noData: '<?php echo htmlentities(addslashes($noResultText))?>',
            root: '<?php echo $parentLink ?>',
            searchPaths: '<?php echo $search_paths ?>',
            min_length: parseInt('<?php echo $minLength?>', 10),
            high_light: <?php echo ($highlight == '1') ? 'true' : 'false' ?>,
            showPath: <?php echo ($showPath == '1') ? 'true' : 'false' ?>,
            classNames: $.parseJSON(<?php echo $classNames?>),
            pageAttributes: $.parseJSON('<?php echo $attributes?>'),
            limit: parseInt('<?php echo $showLimit?>', 10),
            noDataPlaceholder: '<?php echo $controller->noResultTextQueryPlaceHolder ?>',
            cutAfter: parseInt('<?php echo $cutAfter?>', 10),
        }
</script>
<?php
if (isset($error)) {
    ?><?php echo  $error?><br/><br/><?php

}

if (!isset($toesslab_query) || !is_string($toesslab_query)) {
    $toesslab_query = '';
}

?><form id="toesslab-typeahead-form" action="<?php echo  $view->url($resultTarget)?>" method="get" class="ccm-search-block-form"><?php
    if (isset($title) && ($title !== '')) {
        ?><h3><?php echo  h($title)?></h3>
        <?php

    }

    if ($toesslab_query === '') {
        ?><input name="search_paths[]" type="hidden" value="<?php echo  htmlentities($baseSearchPath, ENT_COMPAT, APP_CHARSET) ?>" /><?php

    } elseif (isset($searchPath) && is_array($searchPath)) {
        foreach ($searchPath as $search_path) {
            ?><input name="search_paths[]" type="hidden" value="<?php echo  htmlentities($search_path, ENT_COMPAT, APP_CHARSET) ?>" /><?php

        }
    }
    ?>
    <div id="toesslab-typeahead-container">
        <input name="toesslab" type="hidden" value="1">
        <input id="toesslab_query_<?php echo  $uniqueBID; ?>" name="toesslab_query" type="text" value="<?php echo  htmlentities($toesslab_query, ENT_COMPAT, APP_CHARSET)?>" class="ccm-search-block-text typeahead" />
    </div>
</form>

