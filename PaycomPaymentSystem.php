<?php

Yii::import('ext.payment.paycom.*');

/**
 * Paycom payment system
 */
class PaycomPaymentSystem extends BasePaymentSystem
{


	/**
	 * Generate payme payment form.
	 * @param StorePaymentMethod $method
	 * @param Order $order
	 * @return string
	 */
	public function renderPaymentForm(StorePaymentMethod $method, Order $order)
	{
		$html = '
		<form method="POST" action="http://checkout.paycom.uz" class="payment-method-payme" target="_blank">
			<input type="hidden" name="'. Yii::app()->request->csrfToken .'" value="YII_CSRF_TOKEN">
			<input type="hidden" name="merchant" value="{MERCHANT}" id="merchant">
			<input type="hidden" name="amount" value="{AMOUNT}" id="amount">
			<input type="hidden" name="callback" value="{CALLBACK}" id="callback">
			<input type="hidden" name="account[order_id]" value="{ORDER_ID}" id="order_id">			
			{SUBMIT}
		</form>';

		$settings=$this->getSettings($method->id);

		$html= strtr($html,array(
			'{MERCHANT}' => $settings['merchant_id'],
			'{AMOUNT}'     => 100 * Yii::app()->currency->convert($order->full_price, $method->currency_id),
			'{CALLBACK}'   => 'http://'.$_SERVER['HTTP_HOST'].'/cart/view/'.$order->secret_key ,//Yii::t('core', "Оплата заказа #").$order->id,
			'{ORDER_ID}'    => $order->id, //$settings['LMI_PAYEE_PURSE'],
			'{SUBMIT}'         => $this->renderPaymeSubmit(),
		));

		return $html;
	}

	/**
	 * This method will be triggered after payment method saved in admin panel
	 * @param $paymentMethodId
	 * @param $postData
	 */
	public function saveAdminSettings($paymentMethodId, $postData)
	{
		 $this->setSettings($paymentMethodId, $postData['PaycomConfigurationModel']);
	}

	/**
	 * @param $paymentMethodId
	 * @return string
	 */
	public function getSettingsKey($paymentMethodId)
	{
		 return $paymentMethodId.'_PaycomPaymentSystem';
	}

	/**
	 * Get configuration form to display in admin panel
	 * @param string $paymentMethodId
	 * @return CForm
	 */
	public function getConfigurationFormHtml($paymentMethodId)
	{
		$model = new PaycomConfigurationModel();
		$model->attributes=$this->getSettings($paymentMethodId);
		$form  = new BasePaymentForm($model->getFormConfigArray(), $model);
		return $form;
	}

	/**
	 * Create bill comment contains list of products
	 */
	private function getPaymentComment($order)
	{
		$result = array();

		foreach ($order->products as $product)
			$result[] = $product->name;

		return implode(', ', $result);
	}
	/**
	* @return string
	*/
	protected function renderPaymeSubmit()
	{
		return "<button type='submit' class='payme-submit'><img src='https://help.paycom.uz/images/ru/payme_01_small.png' alt='payme-submit'></button>";
	}
	
	/**
	* Set merchant_id, key into config and password file respectively
	*/
	protected function setAuthConfig($paymentMethodId)
	{
		
	}
}
