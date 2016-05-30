<?php
namespace paymentgate_alphabank\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use yii\helpers\Url;

class PaymentComponent extends Component
{
    public $userName;
    public $password;
    
    public $gatewayUrl;
    
    public $returnUrl;
    public $failUrl;
    
    public $errors = [];
    
    private $_bankOrderId;
    private $_bankFormUrl;
    
    /**
     * список статусов для заказов
     */
    public static function getOrderStatuses()
    {
        return [
            [
                'id' => 0,
                'description' => 'Заказ зарегистрирован, но не оплачен.'
            ],
            [
                'id' => 1,
                'description' => 'Предавторизованная сумма захолдирована (для двухстадийных платежей).'
            ],
            [
                'id' => 2,
                'description' => 'Проведена полная авторизация суммы заказа.'
            ],
            [
                'id' => 3,
                'description' => 'Авторизация отменена.'
            ],
            [
                'id' => 4,
                'description' => 'По транзакции была проведена операция возврата.'
            ],
            [
                'id' => 5,
                'description' => 'Инициирована авторизация через ACS банка-эмитента.'
            ],
            [
                'id' => 6,
                'description' => 'Авторизация отклонена.'
            ]
        ];
    }
    
    /**
     * инициализация платежа в платёжном шлюзе
     * 
     * должны получить id заказа в системе Альфа-Банка
     * и url-адрес формы для реквизитов банк. карты
     * 
     *
     * ЗАПРОС РЕГИСТРАЦИИ ОДНОСТАДИЙНОГО ПЛАТЕЖА В ПЛАТЕЖНОМ ШЛЮЗЕ
     *      register.do
     *
     * ПАРАМЕТРЫ
     *      userName        Логин магазина.
     *      password        Пароль магазина.
     *      orderNumber     Уникальный идентификатор заказа в магазине.
     *      amount          Сумма заказа в копейках.
     *      returnUrl       Адрес, на который надо перенаправить пользователя в случае успешной оплаты.
     *
     * ОТВЕТ
     *      В случае ошибки:
     *          errorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
     *          errorMessage    Описание ошибки.
     *
     *      В случае успешной регистрации:
     *          orderId         Номер заказа в платежной системе. Уникален в пределах системы.
     *          formUrl         URL платежной формы, на который надо перенаправить браузер клиента.
     *
     *  Код ошибки      Описание
     *      0           Обработка запроса прошла без системных ошибок.
     *      1           Заказ с таким номером уже зарегистрирован в системе.
     *      3           Неизвестная (запрещенная) валюта.
     *      4           Отсутствует обязательный параметр запроса.
     *      5           Ошибка значения параметра запроса.
     *      7           Системная ошибка.
     */
    public function initPayment( $systemOrderId, $amount, $description )
    {
        if( is_null($this->returnUrl) ) return false;
        
        $data = [
            'userName' => $this->userName,
            'password' => $this->password,
            'orderNumber' => urlencode($systemOrderId),
            'amount' => urlencode($amount),
            'returnUrl' => Url::home(true).$this->returnUrl,
        ];
        if( !is_null( $this->failUrl ) ) $data['failUrl'] = $this->failUrl;
        
        $response = $this->gateway('register.do', $data);
        
        // В случае ошибки вывести ее
        if( isset($response['errorCode']) && ($response['errorCode'] > 0) ) { 
        
            $this->addError('Error #' . $response['errorCode'] . ': ' . (isset($response['errorMessage'])) ? $response['errorMessage'] : '');
        
        // В случае успеха сохранить номер заказа и formUrl, вернуть true
        } else {
            
            if( !isset($response['orderId']) || !isset($response['formUrl']) ) return false;
            
            $this->_bankOrderId = $response['orderId'];
            $this->_bankFormUrl = $response['formUrl'];
            
            return true;
        
        }
        
        return false;
    }
    
    /**
     * перенаправить пользователя на платёжную форму Альфа-Банка
     */
    public function makePayment( $orderId )
    {
        return false;
    }
    
    /**
     * получить статус платежа 
     */
    public function getStatus( $orderId )
    {
        $data = array(
            'userName' => $this->userName,
            'password' => $this->password,
            'orderId' => $orderId
        );
         
        /**
         * ЗАПРОС СОСТОЯНИЯ ЗАКАЗА
         *      getOrderStatus.do
         *
         * ПАРАМЕТРЫ
         *      userName        Логин магазина.
         *      password        Пароль магазина.
         *      orderId         Номер заказа в платежной системе. Уникален в пределах системы.
         *
         * ОТВЕТ
         *      ErrorCode       Код ошибки. Список возможных значений приведен в таблице ниже.
         *      OrderStatus     По значению этого параметра определяется состояние заказа в платежной системе.
         *                      Список возможных значений приведен в таблице ниже. Отсутствует, если заказ не был найден.
         *
         *  Код ошибки      Описание
         *      0           Обработка запроса прошла без системных ошибок.
         *      2           Заказ отклонен по причине ошибки в реквизитах платежа.
         *      5           Доступ запрещён;
         *                  Пользователь должен сменить свой пароль;
         *                  Номер заказа не указан.
         *      6           Неизвестный номер заказа.
         *      7           Системная ошибка.
         *
         *  Статус заказа   Описание
         *      0           Заказ зарегистрирован, но не оплачен.
         *      1           Предавторизованная сумма захолдирована (для двухстадийных платежей).
         *      2           Проведена полная авторизация суммы заказа.
         *      3           Авторизация отменена.
         *      4           По транзакции была проведена операция возврата.
         *      5           Инициирована авторизация через ACS банка-эмитента.
         *      6           Авторизация отклонена.
         */
        $response = $this->gateway('getOrderStatus.do', $data);
        
        // В случае ошибки вывести ее
        if( isset($response['errorCode']) && ($response['errorCode'] > 0) ) { 
        
            $this->addError('Error #' . $response['errorCode'] . ': ' . (isset($response['errorMessage'])) ? $response['errorMessage'] : '');
        
        // В случае успеха вернуть номер статуса заказа в системе Альфа-Банка
        } else {
            
            if( !isset($response['OrderStatus']) ) return false;
            return $response['OrderStatus'];
        
        }
        
        return false;
    }
    
    /**
     * получить id заказа в системе Альфа-Банка
     */
    public function getOrderId()
    {
        return $this->_bankOrderId;
    }
    
    /**
     * получить адрес формы Альфа-Банка для ввода реквизитов безналичной оплаты
     */
    public function getFormUrl()
    {
        return $this->_bankFormUrl;
    }
    
    /**
     * сформировать адрес страницы для просмотра статуса платежа
     */
    public function formStatusUrl( $orderId )
    {
        return Url::home(true) . Url::to([ $this->returnUrl, 'orderId' => $orderId ]);
    }
    
    /**
     * отправка запроса на платёжный шлюз через Curl (метод POST)
     */
    private function gateway($method, $data)
    {
        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->gatewayUrl . $method, // Полный адрес метода
            CURLOPT_RETURNTRANSFER => true, // Возвращать ответ
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($data) // Данные в запросе
        ));
        $response = curl_exec($curl); // Выполненяем запрос
        
        $response = json_decode($response, true); // Декодируем из JSON в массив
        curl_close($curl); // Закрываем соединение
        return $response; // Возвращаем ответ
    }
    
    /**
     * добавить сообщение об ошибке в массив сообщений об ошибках
     */
    private function addError( $message )
    {
        $this->errors[] = $message;
        return true;
    }
}
