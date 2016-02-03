<?php

namespace paymentgate_alphabank;

use Yii;

use yii\helpers\Url;

class PaymentGateAlphaBank extends \yii\base\Module
{
    public $controllerNamespace = 'paymentgate_alphabank\controllers';

    public $accessClass;
    
    public $paymentClass;
    public $paymentOrderField;
    
    public $componentName;
    
    public $returnUrl;
    public $restartUrl;
    
    /**
     * Number of tasks to be handled at one shot
     */
//    public $tasksAtOnce;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->registerTranslations();
        $this->initComponent();
        
        /*
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'queue\commands';
        }
        */

    }

    /**
     * Initialization of the i18n translation module
     */
    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['paymentgate_alphabank'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@paymentgate_alphabank/messages',

            'fileMap' => [
                'paymentgate_alphabank' => 'paymentgate_alphabank.php',
            ],

        ];
    }
    
    /**
     * @inheritdoc
     */
    public function initComponent()
    {
        $components = Yii::$app->components;
        if( is_null( $this->componentName ) && isset( $components[$this->componentName] ) ) {
            
            $componentName = $this->componentName;
            if( Yii::$app->$componentName instanceof \paymentgate_alphabank\components\PaymentComponent ) {
                Yii::$app->$componentName->returnUrl = Url::toRoute();
            }
            
        }
    }
}
