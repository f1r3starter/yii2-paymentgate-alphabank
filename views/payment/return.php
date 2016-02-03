<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model queue\models\QmQueues */

$this->title = Yii::t('paymentgate_alphabank','Payment processing');

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paymentgate-return">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php if( $payment->isProcess ): ?>
    
        <p><?php echo Yii::t('paymentgate_alphabank','Please, wait. Your payment is processing.'); ?></p>
    
    <?php elseif( $payment->isAccept ): ?>
        
        <p><?php echo Yii::t('paymentgate_alphabank','Your payment is accepted.'); ?></p>
        
    <?php elseif( $payment->isAbort ): ?>
    
        <p><?php echo Yii::t('paymentgate_alphabank','Sorry, your payment has been aborted.'); ?></p>
    
    <?php else: ?>
    
        <p><?php echo Yii::t('paymentgate_alphabank','Your payment not processed. Try to re-enter your payment details.'); ?></p>
    
    <?php endif; ?>
    
    <div class="row">
    
        <div class="col-xs-6">
            <?php if( !empty($paymentGate->returnUrl) ) echo Html::a(Yii::t('paymentgate_alphabank', 'Return'), $paymentGate->returnUrl, ['class' => 'btn btn-primary']); ?>
        </div>
    
        <?php if( $payment->isAbort ): ?>
        
            <div class="col-xs-6 text-right">
                <?php if( !empty($paymentGate->restartUrl) ) echo Html::a(Yii::t('paymentgate_alphabank', 'Restart payment'), $paymentGate->restartUrl, ['class' => 'btn btn-warning']); ?>
            </div>
        
        <?php elseif( $payment->isProcess ): ?>
        
            <div class="col-xs-6 text-right">
                <?php if( !empty($paymentGate->restartUrl) ) echo Html::a(Yii::t('paymentgate_alphabank', 'Refresh payment'), Url::current(), ['class' => 'btn btn-warning']); ?>
            </div>
        
        <?php elseif( !$payment->isAccept ): ?>
        
            <div class="col-xs-6 text-right">
                <?php if( !empty($paymentGate->restartUrl) ) echo Html::a(Yii::t('paymentgate_alphabank', 'Restart payment'), $paymentGate->restartUrl, ['class' => 'btn btn-warning']); ?>
            </div>
        
        <?php endif; ?>
    
    </div>

</div>
