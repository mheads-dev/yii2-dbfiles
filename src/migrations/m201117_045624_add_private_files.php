<?php

use yii\db\Migration;

/**
 * Class m201117_045624_add_private_files
 */
class m201117_045624_add_private_files extends Migration
{
	/**
	 * {@inheritdoc}
	 */
	public function safeUp()
	{
		$this->addColumn(
			'{{%file}}',
			'is_private',
			$this->tinyInteger(1)->notNull()->defaultValue(0)->comment('Is private file')->after('id')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function safeDown()
	{
		$this->dropColumn('{{%file}}', 'is_private');
	}
}
