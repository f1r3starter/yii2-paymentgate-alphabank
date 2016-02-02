<?php

namespace paymentgate_alphabank\controllers;

use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * PaymentController for PaymentGate
 */
class PaymentController extends Controller
{
    public function behaviors()
    {
        return [
            /*
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            */
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    // allow authenticated users
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    // everything else is denied
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action) {
        
        Yii::$app->getModule('wallets');
        Yii::$app->getModule('queue');
        
        return parent::beforeAction($action);
        
    }
    
    /**
     * @return mixed
     */
    public function actionInit()
    {
        return true;
    }
    
    
}
