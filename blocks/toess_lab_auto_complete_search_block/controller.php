<?php
/**
 * Realized with PhpStorm.
 * File: /packages/toess_lab_auto_complete_search/blocks/toess_lab_auto_complete_search_block/controller.php
 * Author: toesslab.ch
 * Date: 19.10.17
 * Time: 00:08
 */
namespace Concrete\Package\ToessLabAutoCompleteSearch\Block\ToessLabAutoCompleteSearchBlock;

use CollectionAttributeKey;
use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\PageList;
use Concrete\Package\ToessLabAutoCompleteSearch\Search\Search;
use Concrete\Package\ToessLabAutoCompleteSearch\Search\Searcher;
use Core;
use Page;
use Request;

/**
 * Class Controller.
 *
 * @package Concrete\Package\ToessLabAutoCompleteSearch\Block\ToessLabAutoCompleteSearchBlock
 */
class Controller extends BlockController
{
    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $baseSearchPath = '';

    /**
     * @var string
     */
    public $minLength = '1';

    /**
     * @var integer
     */
    public $maxResults = 10;

    /**
     * @var string
     */
    public $cutAfter = '150';

    /**
     * @var string
     */
    public $showLimit;

    /**
     * @var string
     */
    public $noResultText = '';

    /**
     * @var string
     */
    public $noResultTextQueryPlaceHolder = '{{query}}';

    /**
     * @var array
     */
    public $reservedParams = ['page=', 'toesslab_query=', 'search_paths[]=', 'submit=', 'search_paths%5B%5D='];

    /**
     * @var string
     */
    protected $btTable = 'btToessLabAutoCompleteSearch';

    /**
     * @var string
     */
    protected $btInterfaceWidth = '400';

    /**
     * @var string
     */
    protected $btInterfaceHeight = '520';

    /**
     * @var string
     */
    protected $btWrapperClass = 'ccm-ui';

    /**
     * @var string
     */
    protected $btDefaultSet = 'form';

    /**
     * @var bool
     */
    protected $btCacheBlockRecord = true;

    /**
     * @var null
     */
    protected $btCacheBlockOutput = null;

    /**
     * @var Connection
     */
    protected $database;

    /**
     * @var Searcher
     */
    protected $search;

    /**
     * @var bool
     */
    protected $highlight = true;

    /**
     * @var bool
     */
    protected $showPath = true;

    /**
    * @var array
    */
    public $attributes = [
        'thumbnail' => '',
        'meta_description' => '',
        'description' => ''
    ];

    /**
    * @var array
    */
    public $pageAttributes = '';

    /**
     * @var array
     */
    protected $classNames = [];

    /**
     * @var array
     */
    public $defaultClassNames = [
        'page-attr-thumbnail' => 'tt-thumbnail',
        'page-attr-meta_description' => 'tt-meta-description',
        'page-attr-description' => 'tt-description',
        'input' => 'tt-input',
        'menu' => 'tt-menu',
        'suggestion' => 'tt-suggestion',
        'open' => 'tt-open',
        'highlight' => 'tt-highlight',
    ];

    /**
     * Used for localization. If we want to localize the name/description we have to include this.
     *
     * @return string
     */
    public function getBlockTypeDescription()
    {
        return t('Add an auto complete search box to your site.');
    }

    /**
     * @return string
     */
    public function getBlockTypeName()
    {
        return t('Auto Complete Search');
    }

    public function on_start()
    {
        parent::on_start();
        $this->database = Core::make('Concrete\Core\Database\Connection\Connection');
        $this->search = new Searcher(Request::getInstance(), $this->database);
        $list = new PageList();
        $this->showLimit = count($list->getResults());
        $this->attributes['thumbnail'] = t('Thumbnail');
        $this->attributes['meta_description'] = t('Meta Description');
        $this->attributes['description'] = t('Description');
        $aks = $this->getAvailableAttributes();
        foreach ($aks as $key => $ak) {
            $this->attributes[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyDisplayName();
        }
        $this->requireAsset('css', 'bootstrap/tooltip');
        $this->requireAsset('javascript', 'bootstrap/tooltip');
    }
    public function getAvailableAttributes()
    {
        return \Concrete\Core\Attribute\Key\CollectionKey::getList();
    }


    /**
     * @return bool
     */
    public function indexExists()
    {
        $numRows = $this->database->GetOne('select count(cID) from PageSearchIndex');

        return $numRows > 0;
    }

    /**
     * @param string $outputContent
     */
    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('toess_lab_auto_complete');
    }

    /**
     *
     */
    public function view()
    {
        $this->set('title', $this->title);
        $this->set('baseSearchPath', $this->baseSearchPath);
        $this->set('minLength', $this->minLength);
        $this->set('cutAfter', $this->cutAfter);
        $this->set('showLimit', $this->showLimit);
        $this->set('noResultText', $this->noResultText);
        $this->set('highlight', ($this->highlight == 1) ? '1' : '0');
        $this->set('showPath', ($this->showPath == 1) ? '1' : '0');
        $this->set('classNames', (count([$this->classNames]) > 0) ? json_encode($this->classNames) : json_encode($this->defaultClassNames));
        $this->set('request', Core::make(\Concrete\Core\Http\Request::class));
        $this->set('attributes', json_encode($this->attributes));
        //run query if display results elsewhere not set, or the cID of this page is set
        if ((string) $this->request->request('toesslab_query') !== '') {
            if ((string) $this->request->request('toesslab_query') !== '' || $this->request->request('akID') || $this->request->request('month')) {
                $this->do_type_ahead_search();
            }
        }
    }

    /**
     * Method called when the "add block" dialog is going to be shown.
     */
    public function add()
    {
        $this->edit();
    }

    /**
     * Method called when the "edit block" dialog is going to be shown.
     */
    public function edit()
    {
        $this->set('pageSelector', Core::make('helper/form/page_selector'));

        $this->set('pageAttributes', (is_null(json_decode($this->pageAttributes, true)) || $this->pageAttributes == '') ? [] : json_decode($this->pageAttributes, true));
        $this->set('attributes', $this->attributes);
    }

    public function validate($args)
    {
        $e = Core::make('helper/validation/error');
        if ($args['queryPlaceholder'] == '1') {
            if (strpos($args['noResultText'], $this->noResultTextQueryPlaceHolder) === false) {
                $e->add(t('You\'ve inserted the query placeholder but it isn\'t formatted correctly. It should be %s', $this->noResultTextQueryPlaceHolder));
            }
        }
        if (intval($args['maxResults']) < 1) {
            $e->add(t('The \'Maximum results shown\' number must be 1 or greater'));
        }
        return $e;
    }

    /**
     * @param array $data
     */
    public function save($data)
    {
        $validator = Core::make('helper/security');
        $data += [
            'searchUnderCID' => 0,
        ];
        $r = [];
        foreach ($data as $k => $d) {
            if (strpos($k, 'page_attr') === false) {
                continue;
            }
            $r[][substr($k, 10)] = $d;
        }
        $args = [];
        $args['queryPlaceholder'] = ($data['queryPlaceholder'] === '') ? 0 : $data['queryPlaceholder'];
        $args['pageAttributes'] = json_encode($r);
        $args['minLength'] = ((int) $data['minLength'] >= 0 && strlen($data['minLength']) > 0) ? $data['minLength'] : '1';
        $args['maxResults'] = ((int) $data['maxResults'] >= 0 && strlen($data['maxResults']) > 0) ? $data['maxResults'] : '10';
        $args['cutAfter'] = ((int) $data['cutAfter'] >= 0 && strlen($data['cutAfter']) > 0) ? $data['cutAfter'] : '150';
        $args += [
            'title' => $validator->sanitizeString($data['title']),
            'baseSearchPath' => '',
            'noResultText' => $validator->sanitizeString($data['noResultText']),
        ];
        $args['highlight'] = ($data['highlight'] == '1') ? 1 : 0;
        $args['showPath'] = ($data['showPath'] == '1') ? 1 : 0;
        $cN = (count($data['className']) > 0) ? $data['className'] : $this->defaultClassNames;
        $args['classNames'] = json_encode($cN);
        switch ($data['baseSearchPath']) {
            case 'THIS':
                $c = Page::getCurrentPage();
                if (is_object($c) && !$c->isError()) {
                    $args['baseSearchPath'] = (string) $c->getCollectionPath();
                }
                break;
            case 'OTHER':
                if ($data['searchUnderCID']) {
                    $searchUnderCID = (int) $data['searchUnderCID'];
                    $searchUnderC = Page::getByID($searchUnderCID);
                    if (is_object($searchUnderC) && !$searchUnderC->isError()) {
                        $args['baseSearchPath'] = (string) $searchUnderC->getCollectionPath();
                    }
                }
                break;
}
        if (trim($args['baseSearchPath']) === '/') {
            $args['baseSearchPath'] = '';
        }
        parent::save($args);
    }

    /**
     * @param null $b
     * @return null
     */
    public function getBlockUID($b = null) {
        if ($b==null) return null;
        $proxyBlock = $b->getProxyBlock();
        return $proxyBlock? $proxyBlock->getBlockID() : $b->bID;
    }

    /**
     * @return array|\Traversable
     */
    public function do_type_ahead_search()
    {
        $query = (string) $this->request->request('toesslab_query');

        $ipl = new PageList();
        $aksearch = false;
        $akIDs = $this->request->request('akID');
        if (is_array($akIDs)) {
            foreach ($akIDs as $akID => $req) {
                $fak = CollectionAttributeKey::getByID($akID);
                if (is_object($fak)) {
                    $type = $fak->getAttributeType();
                    $cnt = $type->getController();
                    $cnt->setAttributeKey($fak);
                    $cnt->searchForm($ipl);
                    $aksearch = true;
                }
            }
        }

        if ($this->request->request('month') !== null && $this->request->request('year') !== null) {
            $year = @(int) ($this->request->request('year'));
            $month = abs(@(int) ($this->request->request('month')));
            if (strlen(abs($year)) < 4) {
                $year = (($year < 0) ? '-' : '') . str_pad(abs($year), 4, '0', STR_PAD_LEFT);
            }
            if ($month < 12) {
                $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            }
            $daysInMonth = date('t', strtotime("$year-$month-01"));
            $dh = Core::make('helper/date');
            /* @var $dh \Concrete\Core\Localization\Service\Date */
            $start = $dh->toDB("$year-$month-01 00:00:00", 'user');
            $end = $dh->toDB("$year-$month-$daysInMonth 23:59:59", 'user');
            $ipl->filterByPublicDate($start, '>=');
            $ipl->filterByPublicDate($end, '<=');
            $aksearch = true;
        }

        if ($query === '' && $aksearch === false) {
            return false;
        }

        if ($query !== '') {
            $ipl->filterByKeywords($query);
        }

        $search_paths = $this->request->request('search_paths');
        if (is_array($search_paths)) {
            foreach ($search_paths as $path) {
                if ($path === '') {
                    continue;
                }
                $ipl->filterByPath($path);
            }
        } elseif ($this->baseSearchPath != '') {
            $ipl->filterByPath($this->baseSearchPath);
        }

        $cak = CollectionKey::getByHandle('exclude_search_index');
        if (is_object($cak)) {
            $ipl->filterByExcludeSearchIndex(false);
        }

        $pagination = $ipl->getPagination();
        $pagination->setMaxPerPage(intval($this->maxResults));
        $res = $pagination->getCurrentPageResults();
        foreach ($res as $result) {
            $results[$result->getCollectionID()] = $result->getPageIndexContent();
        }
        $this->set('toesslab_query', $query);
        $this->set('search_paths', $search_paths);
        $this->set('results', $results);
        $this->set('searchList', $ipl);
        $this->set('pagination', $pagination);
        $this->set('do_type_ahead_search', true);
        $this->set('do_type_ahead_search', true);

        return $results;
    }

    /**
     * From the ajax call: Fires all the search operations.
     */
    public function action_get_results()
    {
        $pageResults = $this->do_type_ahead_search();
        if (!is_array($pageResults) || count($pageResults) == 0) {
            echo json_encode([]);
            exit;
        }
        $words = $this->search->getContentResults($pageResults, $this->pageAttributes);
        echo json_encode($words);
        exit;
    }

}
