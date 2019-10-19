<?php
/**
 * Realized with PhpStorm.
 * File: /packages/toess_lab_auto_complete_search/src/Concrete/Search/Searcher.php
 * Author: toesslab.ch
 * Date: 19.10.17
 * Time: 00:08
 */

namespace Concrete\Package\ToessLabAutoCompleteSearch\Search;

use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\Page;
use Request;

/**
 * Class Searcher
 * @package Concrete\Package\ToessLabAutoCompleteSearch\Search
 */
class Searcher
{

    /**
     * @var
     */
    protected $request;

    /**
     * @var
     */
    protected $database;

    /**
     * Searcher constructor.
     * @param Request $request
     * @param Connection $connection
     */
    function __construct(Request $request, Connection $connection)
    {
        $this->request = $request;
        $this->database = $connection;
    }

    /**
     * @param array $pageResults
     * @param array $pageAttributes
     * @return array
     */
    public function getContentResults(array $pageResults, $pageAttributes = [])
    {
        $query = (string) $this->request->request('toesslab_query');
        $inQuery = implode(',', array_fill(0, count($pageResults), '?'));
        $stmt = $this->database->prepare('SELECT cID, cName, cPath, cDescription FROM PageSearchIndex 
          WHERE cID IN (' . $inQuery . ')');
        $stmt->execute(array_keys($pageResults));
        $result = $stmt->fetchAll();
        $words = [];
        $pageAttr = ($pageAttributes == 'null') ? [] : json_decode($pageAttributes, true);
        foreach ($result as $res){
            $index = $res['cID'];
            unset($res['cID']);
            $res['content'] = $pageResults[$index];
            $words[$index] = $this->createSentenceResults($index, $res, $query, $pageAttr);
        }
        return $words;
    }

    /**
     * @param $index
     * @param $res
     * @param $query
     * @param $pageAttributes
     * @return array
     */
    private function createSentenceResults($index, $res, $query, $pageAttributes = [])
    {
        $sentencePattern = '/(?<!\w\.\w.)(?<![A-Z][a-z][\s\n\r\t]\.)(?<=\.|\?)\s/';
        $sentenceURL = '/\?|!|\r\n\r\n|\n|\n\n/';
        $needle = ['|', '/', ',', '&', '-', '[', ']', '.', '?', '!', 'http'];
        $words = [];
        $page = Page::getByID($index);
        $check_url = parse_url($res['content']);
        $path = (is_null($res['cPath'])) ? '' : $res['cPath'];
        if(array_key_exists('scheme', $check_url)) {
            $res['sentences'] = preg_split($sentenceURL, implode('|||', $res));
        } else {
            $res['sentences'] = preg_split($sentencePattern, implode('|||', $res));
        }
        foreach($res['sentences'] as $k => $s) {
            $sentence[$k] = preg_split('/\|\|\|/', $s);
        }
        $res['sentences'] = $this->flattenArray($sentence, []);
        $tmpArr = array_diff($res['sentences'], $needle);
        for($i = 0; $i < count($tmpArr); ++$i) {
            preg_match("/$query/i", $tmpArr[$i], $arrIndex);
            if(count($arrIndex) > 0) {
                $words['value'][] = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $tmpArr[$i]);
            }
        }
        $words['id'] = $index;
        $words['cPath'] = $path;
        $words['name'] = (strlen($res['cName']) > 0) ? $res['cName'] : $path;
        if (is_array($pageAttributes) && sizeof($pageAttributes) > 0) {
            foreach ($pageAttributes as $pageAttribute) {
                foreach ($pageAttribute as $handle => $p) {
                    /**
                     * Built in page properties
                     * https://documentation.concrete5.org/developers/working-with-pages/getting-data-about-a-page
                     */
                    if ($handle == 'description') {
                        if (strlen($page->getCollectionDescription()) > 0) {
                            $words[$handle] = $page->getCollectionDescription();
                        }
                    } else {
                        $words[$handle] = $this->getAttributeValues($page, $handle);
                    }
                }
            }
        }

        return $words;
    }

    private function flattenArray($array, $return)
    {
        for($i = 0; $i <= count($array); $i++) {
            if(is_array($array[$i])) {
                $return = $this->flattenArray($array[$i], $return);
            }
            else {
                if(isset($array[$i])) {
                    $return[] = $array[$i];
                }
            }
        }
        return $return;
    }

    /**
     * @param Page $page
     * @param $handle
     * @return string
     */
    private function getAttributeValues(Page $page, $handle)
    {
        if (!is_object($page)) {
            return '';
        }
        $values = [];
        if (!is_null($page->getAttribute($handle))) {
            return $values[$handle] = $page->getAttribute($handle, 'display');
        }
        return '';
    }

}
