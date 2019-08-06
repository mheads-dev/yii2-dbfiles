<?php
/**
 * Created by PhpStorm.
 * User: Alexeenko Sergey Aleksandrovich
 * Email: sergei_alekseenk@list.ru
 * Company: http://machineheads.ru
 * @see https://github.com/mheads-dev/yii2-dbfiles
 * Date: 19.03.2019
 * Time: 12:08
 */

namespace mheads\dbfiles;

class ImageFile extends File
{
	/** @var int */
	protected $validateMinWidth;
	/** @var int */
	protected $validateMaxWidth;
	/** @var int */
	protected $validateMinHeight;
	/** @var int */
	protected $validateMaxHeight;

	public function rules()
	{
		$rules = parent::rules();
		$rules['file'][1] = 'image';

		if($this->validateMinWidth !== NULL)
		{
			$rules['file']['minWidth'] = $this->validateMinWidth;
		}
		if($this->validateMaxWidth !== NULL)
		{
			$rules['file']['maxWidth'] = $this->validateMaxWidth;
		}
		if($this->validateMinHeight !== NULL)
		{
			$rules['file']['minHeight'] = $this->validateMinHeight;
		}
		if($this->validateMaxHeight !== NULL)
		{
			$rules['file']['maxHeight'] = $this->validateMaxHeight;
		}

		return $rules;
	}

	/**
	 * @param $value int
	 */
	public function setValidateMinWidth($value)
	{
		$this->validateMinWidth = $value;
	}
	/**
	 * @param $value int
	 */
	public function setValidateMaxWidth($value)
	{
		$this->validateMaxWidth = $value;
	}
	/**
	 * @param $value int
	 */
	public function setValidateMinHeight($value)
	{
		$this->validateMinHeight = $value;
	}
	/**
	 * @param $value int
	 */
	public function setValidateMaxHeight($value)
	{
		$this->validateMaxHeight = $value;
	}
}