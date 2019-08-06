<?php

use yii\db\Migration;

/**
 * Class m190319_025425_file_storage_init
 */
class m190319_025425_file_storage_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
    	$this->execute("
CREATE TABLE {{%file}} (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`subdir` VARCHAR(255) NULL DEFAULT NULL,
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`original_name` VARCHAR(255) NULL DEFAULT NULL,
	`group_name` VARCHAR(50) NULL DEFAULT NULL,
	`heigh` INT(18) NULL DEFAULT NULL,
	`width` INT(18) NULL DEFAULT NULL,
	`file_size` BIGINT(20) NULL DEFAULT NULL,
	`content_type` VARCHAR(255) NULL DEFAULT NULL,
	`description` VARCHAR(255) NULL DEFAULT NULL,
	`created_at` INT(11) NULL DEFAULT NULL,
	`updated_at` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `subdir` (`subdir`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
    	");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->dropTable('file');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190319_025425_file_storage_init cannot be reverted.\n";

        return false;
    }
    */
}
