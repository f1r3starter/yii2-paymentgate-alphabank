<?php
namespace paymentgate-alphabank\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use wallets\models\Payments;

class PaymentComponent extends Component
{
    public $userName;
    public $password;
    
    public $returnUrl;
    public $failUrl;
    
    private $_payment;
    
    private $_bankOrderId;
    private $_bankFormUrl;
    
    /**
     * инициализация платежа
     */
    public function initPayment( $toWallet, $amount, $description )
    {
        $this->_payment = new Payments([
            'to_wallet' => $toWallet,
            'initiator_id' => Yii::$app->user->id,
            'amount' => $amount,
            'description' => $description,
        ]);
        $this->_payment->setPaymentsScheme('alfabank');
        
        if( $this->_payment->validate() && $this->_payment->nextStep() ) {
            
            $handler = $this->_payment->scheme->handler;
            $response = $handler->init( $this->_payment, $this->userName, $this->password, $this->returnUrl, $this->failUrl );
            if( $response !== false ) {
                
                $this->_bankOrderId = $response['orderId'];
                $this->_bankFormUrl = $response['formUrl'];
                return true;
                
            }
            
        }
        
        return false;
    }
    
    /**
     * 
     */
    public function makePayment( $id )
    {
        return false;
    }
    
    /**
     * получить статус платежа 
     */
    public function getStatus( $id )
    {
        return false;
    }
}
