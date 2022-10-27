<?php

namespace webdna\idme\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\Db;

use craft\commerce\db\Table;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%idme_discounts}}');
        if ($tableSchema === null) {
            $this->createTable(
                '{{%idme_discounts}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'discountId' => $this->integer()->notNull(),
                    'groups' => $this->string(255)
                ]
            );
            $this->createIndex(null, '{{%idme_discounts}}', 'id', false);
            $this->addForeignKey(null, '{{%idme_discounts}}', ['discountId'], Table::DISCOUNTS, ['id'], 'CASCADE', null);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        Db::dropAllForeignKeysToTable('{{idme_discounts}}', $this->db);
        $this->dropTableIfExists('{{%idme_discounts}}');

        return true;
    }

}