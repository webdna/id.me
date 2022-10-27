<?php

namespace webdna\idme\records;

use craft\db\ActiveRecord;

/**
 * Site Variant record.
 *
 * @property int $id
 * @property int $discountId
 * @property string $groups
 */
class IdMeDiscount extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%idme_discounts}}';
    }

}