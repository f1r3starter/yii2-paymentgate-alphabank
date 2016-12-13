<?php

namespace paymentgate_alphabank\controllers;

use paymentgate_alphabank\components\PaymentComponent;
use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use yii\helpers\ArrayHelper;

use paymentgate_alphabank\PaymentGateAlphaBank;

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
    public function beforeAction($action)
    {
        Yii::$app->getModule('wallets');
        Yii::$app->getModule('queue');

        return parent::beforeAction($action);
    }

    /**
     * @param $orderId
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionReturn($orderId)
    {
        $paymentGate = PaymentGateAlphaBank::getInstance();

        $paymentClass = $paymentGate->paymentClass;
        // здесь надо поставить Exception, если нет $paymentClass

        $payment = $paymentClass::find()->where([$paymentGate->paymentOrderField => $orderId])->one();
        if (empty($payment)) throw new NotFoundHttpException(Yii::t('paymentgate_alphabank', 'The requested page does not exist.'));

        $orderStatus = Yii::$app->paymentgate_alphabank->getStatus($orderId);

        return $this->render('return', [
            'paymentGate' => $paymentGate,
            'payment' => $payment,
            'orderStatusDescription' => PaymentComponent::getOrderStatuses($orderStatus),
        ]);
    }


}
