<?php
/**
 * Realized with PhpStorm.
 * File: /packages/toess_lab_auto_complete_search/controller.php
 * Author: toesslab.ch
 * Date: 19.10.17
 * Time: 00:08
 */
namespace Concrete\Package\ToessLabAutoCompleteSearch;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Package\Package;

/**
 * Class Controller
 * @package Concrete\Package\ToessLabAutoCompleteSearch
 */
class Controller extends Package
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @var string
     */
    protected $pkgHandle = 'toess_lab_auto_complete_search';

    /**
     * @var string
     */
    protected $appVersionRequired = '5.7.5.13';

    /**
     * @var string
     */
    protected $pkgVersion = '1.10.2';

    /**
     * @var bool
     */
    protected $pkgAutoloaderMapCoreExtensions = true;

    /**
     * @return string
     */
    public function getPackageDescription()
    {
        return t('Auto complete search while typing in search terms.');
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return t('toesslab - Auto Complete Search');
    }

    /**
     * @return string
     */
    public function getPackageHandle()
    {
        return $this->getPkg();
    }

    /**
     * Installs the package.
     */
    public function install()
    {
        $pkg = parent::install();
        BlockType::installBlockType('toess_lab_auto_complete_search_block', $pkg);
    }

    /**
     *
     */
    public function on_start()
    {
        $pkg = $this;
        $al = AssetList::getInstance();
        $al->register(
            'javascript', 'toess_lab_auto_complete-bloodhound', 'js/libs/typeahead/bloodhound.min.js', ['position' => Asset::ASSET_POSITION_FOOTER], $pkg
        );
        $al->register(
            'javascript', 'toess_lab_auto_complete-bundle', 'js/libs/typeahead/typeahead.bundle.min.js', ['position' => Asset::ASSET_POSITION_FOOTER], $pkg
        );
        $al->register(
            'javascript', 'toess_lab_auto_complete-jquery', 'js/libs/typeahead/typeahead.jquery.min.js', ['position' => Asset::ASSET_POSITION_FOOTER], $pkg
        );
        $al->register(
            'css', 'toess_lab_auto_complete', 'js/libs/typeahead/typeahead.css', ['position' => Asset::ASSET_POSITION_HEADER], $pkg
        );
        $al->registerGroup('toess_lab_auto_complete', [
            ['javascript', 'toess_lab_auto_complete-bloodhound'],
            ['javascript', 'toess_lab_auto_complete-bundle'],
            ['javascript', 'toess_lab_auto_complete-jquery'],
            ['css', 'toess_lab_auto_complete'],
        ]);
    }

    /**
     * @return string
     */
    private function getPkg()
    {
        return $this->pkgHandle;
    }
}
