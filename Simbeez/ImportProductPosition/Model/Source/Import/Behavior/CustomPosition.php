<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Simbeez\ImportProductPosition\Model\Source\Import\Behavior;

use Magento\ImportExport\Model\Import;

/**
 * Import behavior source model used for defining the behaviour during the position import.
 */
class CustomPosition extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            Import::BEHAVIOR_APPEND => __('Add/Update')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return 'customposition';
    }

    /**
     * @inheritdoc
     */
    public function getNotes($entityCode)
    {
        $messages = ['catalog_product' => [
            Import::BEHAVIOR_APPEND => __(
                "New product position data is added to the existing product data for the existing entries in the database. "
                . "Position can be updated."
            ),
        ]];
        return isset($messages[$entityCode]) ? $messages[$entityCode] : [];
    }
}
