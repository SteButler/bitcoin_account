<?php
/**
* Bitcoin daemon control with accounts.
* @author m0Ray <m0ray@nm.ru>
* @version 1.0
* @package BitcoinShop
*/

/**
* Generic account class
* @package BitcoinShop
*/
class BitcoinAccount
{

 /**
 * jsonRPCClient instance
 * @var jsonRPCClient
 */
 protected $rpc=NULL;

 /**
 * Account ID
 * @var integer|string
 */
 protected $account='';

 /**
 * Class constructor
 * @param string|jsonRPCClient $rpc_url JSON-RPC URL or class instance for connection to bitcoind.
 * @param string|integer $account Account ID. Empty string is system default account.
 */
 public function __construct($rpc,$account='')
 {
  if($rpc instanceof jsonRPCClient)
  {
   $this->rpc=$rpc;
  }
  else
  {
   $this->rpc=new jsonRPCClient($rpc);
  }
  if($this->rpc)
  {
   if($account)
   {
    $this->account=$account;
   }
  }
  else
  {
   throw new Exception('BitcoinAccount: cannot connect to bitcoin JSON-RPC.');
  }
 }

 /**
 * Get recently created bitcoin address for user. Create it if there is none.
 * @param boolean $new Force create
 * @return string|NULL
 */
 public function getAddress($new=false)
 {
  if($new)
  {
   return $this->rpc->getnewaddress($this->account);
  }
  else
  {
   $addrs=$this->getAddressList();
   if(count($addrs))
   {
    return $addrs[count($addrs)-1];
   }
   else
   {
    return $this->getAddress(true);
   }
  }
 }

 /**
 * Get all bitcoin addresses for account.
 * @return array|NULL
 */
 public function getAddressList()
 {
  return $this->rpc->getaddressesbyaccount($this->account);
 }

 /**
 * Get last N transactions for account.
 * @param integer $N Defaults to 10
 * @return array|NULL
 */
 public function getTransactionList($N=10)
 {
  return $this->rpc->listtransactions($this->account,$N);
 }

 /**
 * Get account balance.
 * @return float
 */
 public function getBalance()
 {
  return $this->rpc->getbalance($this->account);
 }


 /**
 * Transfer specified amount out of user's account
 * @param string $target_addr Target bitcoin address
 * @param float|integer $amount Amount to transfer.
 * @param string $comment Transfer comment
 * @return boolean
 */
 public function transfer($target_addr,$amount)
 {
  $valid=$this->rpc->validateaddress($target_addr);
  if($valid['isvalid'])
  {
   return $this->rpc->sendfrom($this->account,$target_addr,floatval($amount));
  }
 }

 /**
 * Transfer specified amount to another user in same system
 * @param string $target_addr Target account
 * @param float|integer $amount Amount to transfer.
 * @return boolean
 */
 public function transferInternal($target_account,$amount)
 {
  return $this->rpc->move($this->account,$target_account,floatval($amount));
 }

}
