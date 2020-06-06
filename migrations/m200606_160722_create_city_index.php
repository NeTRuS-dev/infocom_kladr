<?php

use yii\db\Migration;

/**
 * Class m200606_160722_create_city_index
 */
class m200606_160722_create_city_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex(
            'idx-city-CODE',
            'city'
        );
        $this->createIndex(
            'idx-city-CODE-SOCR',
            'city',
            ['CODE', 'SOCR']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-city-CODE-SOCR',
            'city'
        );
        $this->createIndex(
            'idx-city-CODE',
            'city',
            'CODE'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200606_160722_create_city_index cannot be reverted.\n";

        return false;
    }
    */
}
