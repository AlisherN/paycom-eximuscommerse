<?php

namespace Paycom;

class MerchantConfig extends Database
{
	/**
	* merchant_id property for paycom auth
	*/
	private $_merchant_id;
	
	/**
	* key property for paycom auth
	*/
	private $_key;
	
	/**
	* construct function
	*/
	public function __construct()
	{
		
	}
	
	/**
	* Function that uses other functions to get paycom data
	* @return array $config
	*/
	public function getConfigurations()
	{
		$config = $this->getConfigFromDb();		
		return $config;
	}
	
	/**
	* Getting merchant_id value
	* @return merchant_id
	*/
	protected function getMerchantId()
	{
		return $this->_merchant_id;
	}
	
	/**
	* Getting marchant key value
	* @return key
	*/
	protected function getMerchantKey()
	{
		return $this->_key;
	}
	
	/**
	* Setting merchant_id from db to $_merchant_id property
	*/
	protected function setMerchantId($merchantId)
	{
		$this->_merchant_id = $merchantId;
	}
	
	/**
	* Setting key from db to $_key property
	*/
	protected function setMerchantKey($merchantKey)
	{
		$this->_key = $merchantKey;
	}
	
	/**
	* Get merchant_id, key from db, and add login of paycom
	* @return array $config with merchant_id, key and login
	*/
	protected function getConfigFromDb()
	{
		$config = array();
		
		$sql = "SELECT * FROM db_name.SystemSettings WHERE category LIKE ?";
		$q = self::db()->prepare($sql);
		$q->execute(['%PaycomPaymentSystem']);
		
		
		while($r = $q->fetch())
		{
			$config[$r['key']] = $r['value'];
		}
		
		$config['login']	   = 'Paycom';
		
		array_values($config);
		
		return $config;
	}
}