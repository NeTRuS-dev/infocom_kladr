<?php

use yii\db\Migration;

/**
 * Class m200606_160211_create_socrbase_index
 */
class m200606_160211_create_socrbase_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createIndex(
            'idx-socrbase-LEVEL-SOCRNAME',
            'socrbase',
            ['LEVEL', 'SOCRNAME']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-socrbase-LEVEL-SOCRNAME',
            'socrbase'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200606_160211_create_socrbase_index cannot be reverted.\n";

        return false;
    }
    */
}
