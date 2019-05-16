<?php

class PaycomConfigurationModel extends CModel
{

	public $merchant_id;
	public $key;

	/**
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('merchant_id, key', 'type')
		);
	}

	/**
	 * @return array
	 */
	public function attributeNames()
	{
		return array(
			'merchant_id'  => Yii::t('PaycomPaymentSystem', 'Paycom merchant ID Кошелька'),
			'key' => Yii::t('PaycomPaymentSystem', 'Secret key'),
		);
	}

	/**
	 * @return array
	 */
	public function getFormConfigArray()
	{

		return array(
			'type'=>'form',
			'elements'=>array(
				'merchant_id'=>array(
					'label'=>Yii::t('PaycomPaymentSystem', 'Merchant ID'),
					'type'=>'text',
					'hint'=>'Пример: 5c785198f201de247e89t056',
				),
				'key'=>array(
					'label'=>Yii::t('PaycomPaymentSystem', 'Пароль'),
					'type'=>'text',
				),
		));
	}
}
