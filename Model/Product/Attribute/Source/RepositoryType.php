<?php
/**
 * @package     Dadolun_Repman
 * @copyright   Copyright (c) 2023 Dadolun (https://www.dadolun.com)
 * @license     This code is licensed under MIT LICENSE (see LICENSE for details)
 */
namespace Dadolun\Repman\Model\Product\Attribute\Source;

/**
 * Class RepositoryType
 * @package Dadolun\Repman\Model\Product\Attribute\Source
 */
class RepositoryType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Git'), 'value' => 'git'],
                ['label' => __('GitHub'), 'value' => 'github'],
                ['label' => __('GitLab'), 'value' => 'gitlab'],
                ['label' => __('Bitbuket'), 'value' => 'bitbucket'],
                ['label' => __('Mercurial'), 'value' => 'mercurial'],
                ['label' => __('Subversion'), 'value' => 'subversion'],
                ['label' => __('Pear'), 'value' => 'pear'],
            ];
        }
        return $this->_options;
    }
}
