<?php
namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\Testdata;
use frontend\models\Fakedata;
use frontend\models\Settings;
use yii\httpclient\Client;
use yii\httpclient\JsonParser;
use yii\web\Cookie;
use yii\web\CookieCollection;


use yii\httpclient\FormatterInterface;
use yii\httpclient\ParserInterface;
use yii\httpclient\Response;




ini_set("max_execution_time","18000" );
ini_set("memory_limit", "13500M");
date_default_timezone_set('Europe/Kiev');

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
	
	

	/**
     *  Cipher Создание и проверка подписи
     *
     * @return mixed
     */
    public function actionCipher()
    {
	
	$date = new \DateTime();
	
	
	/**
	*   Obtaine settings data
	**/
	
	
	$settings = new Settings;
	
	//$url = $settings->find()->where(['option' => 'url'])->one();
	//$counter = $settings->find()->where(['option' => 'counter'])->one();
	//$cert = $settings->find()->where(['option' => 'cert'])->one();
	
	//$cert = $cert->value;
	//$url = $url->value;
    //$counter = intval($counter->value);
    
    $cert = 'TEST CERT';
	$url = 'CIPHER.COM';
	$counter = 10;
	
	$i = 0;
	
		while ($i <> $counter) {

		$client = new Client([
			//'baseUrl' => 'http://signer-service-cipher-00.apps.cl02.core.local/', 
			'baseUrl' => $url, 
			'transport' => 'yii\httpclient\CurlTransport',

			'requestConfig' => [
			'format' => Client::FORMAT_JSON
			
			],
			'responseConfig' => [
			'format' => Client::FORMAT_JSON
			],
		]);
		
	
	
	// Step 1: Старт сесии
	// $request_start_session = $client->createRequest();
	

		$response = $client->createRequest()
			->setUrl('api/v1/ticket')
			->setHeaders(['content-type' => 'application/json'])
			->setMethod('POST')
			->send();
		
		$cipher_cookie = $response->getCookies()->get('cipher-http-01');
		
		if ($response->isOk) {
			$message = $response->data['message'];
			$ticketUuid = $response->data['ticketUuid'];
		}
		else {
			$message = 'Error';
			$ticketUuid = 'No session created';
		}
		
		// Add entry to Database
		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 1| Create session';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();

		unset($response);
		
	// Step 1 END
	
	// Проверка подписание документа
	
	
	// Step 2: Загрузить данные сессии
	
		$response = $client->createRequest()
			->setUrl('api/v1/ticket/' . $ticketUuid . '/data')
			->addHeaders(['Content-type' => 'application/json'])
			->setMethod('POST')
			->addCookies([['name' => 'cipher-http-01', 'value' => $cipher_cookie]])
			->setContent('{"base64Data": "0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM"}')
			->send();
		
		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 2| Загрузить данные сессии';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();	
	
	// END Step 2
	
	
	
	// Step 3: Загрузить данные ЭЦП
	
		$response = $client->createRequest()
			->setUrl('api/v1/ticket/' . $ticketUuid . '/ds/data')
			->addHeaders(['Content-type' => 'application/json'])
			->setMethod('POST')
			->addCookies([['name' => 'cipher-http-01', 'value' => $cipher_cookie]])
			->setContent('{"base64Data": "' . $cert . '"}')
			->send();
		
		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 3| Загрузить данные ЭЦП';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();	
	// END Step 3
	

	
	// Step 4: Установить параметры сессии
	
		$response = $client->createRequest()
			->setUrl('api/v1/ticket/' . $ticketUuid . '/option')
			->addHeaders(['Content-type' => 'application/json'])
			->setMethod('PUT')
			->addCookies([['name' => 'cipher-http-01', 'value' => $cipher_cookie]])
			->setContent('{"embedCertificateType" : "signerCert"}');
			
			$response->send();
		
		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 4| Установить параметры сессии';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();		
	// END Step 4
	 

	// Step 5: Проверить ЭЦП
				
		$last_line = shell_exec('curl --location --request POST "' . $url . 'api/v1/ticket/' .$ticketUuid. '/ds/verifier" \
					--header "Content-Type: text/plain" \
					--header "Cookie: cipher-http-01=' .$cipher_cookie. '" \
					--data-raw ""');

		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 5| Проверить ЭЦП';
		$TestData->response_string = $last_line;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();	
	// END Step 5

	
	// Step 6: Получить подписанные документы
	
		$response = $client->createRequest()
			->setUrl('api/v1/ticket/' . $ticketUuid . '/ds/base64SignedData')
			->setMethod('GET')
			->addCookies([['name' => 'cipher-http-01', 'value' => $cipher_cookie]])
			->send();
		
		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 6| Получить подписанные документы';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();	
	// END Step 6
	
	
	// Step 7: Получить результат проверки ЭЦП
	
		$response = $client->createRequest()
			->setUrl('api/v1/ticket/' . $ticketUuid . '/ds/verifier')
			->addHeaders(['content-type' => 'text/plain'])
			->setMethod('GET')
			->addCookies([['name' => 'cipher-http-01', 'value' => $cipher_cookie]])
			->send();
		
		$TestData = new Testdata();
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 5| Проверить ЭЦП';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();	
	// END Step 7
	
	
	
	
	

	// Step 8: Удаление сессии
	
		$response = $client->createRequest()
			->setUrl('api/v1/ticket/' . $ticketUuid)
			->addHeaders(['content-type' => 'application/json'])
			->setMethod('DELETE')
			->addCookies([['name' => 'cipher-http-01', 'value' => $cipher_cookie]])
			//->setContent('{query_string: "Yii"}')
			->send();
		
		$TestData = new Testdata;
		$TestData->user = 'IT Specialist';
		$TestData->session_counter = $i;
		$TestData->session_cipher = $ticketUuid;
		$TestData->request_string = 'Step 7| Delete session';
		$TestData->response_string = $response->content;
		$TestData->data = $date->format('Y-m-d H:i:s');
		$TestData->save();
	//Step 8: END	
		
		$i++;
		unset ($response);	
		unset ($client);	

		}
	
        return $this->render('cipher2', ['counter'=>$i]);
        			
    }
	
	
	

    /**
     * Signs user up.
     *
     * @return mixed
     */
	 
    public function actionPrepare()
    {
        $payload = '0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGMDQrRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgCHRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YwNCtGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGAIdGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjA0K0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YAh0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGMDQrRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgCHRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YwNCtGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGAIdGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjA0K0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YAh0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGMDQrRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgCHRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YwNCtGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGAIdGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjA0K0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YAh0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGMDQrRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgCHRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YwNCtGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGAIdGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjA0K0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YAh0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGMDQrRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgCHRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YwNCtGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGAIdGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjNGI0LjRhNGA0L7QstCw0YLRjA0K0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YAh0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGM0YjQuNGE0YDQvtCy0LDRgtGMDQrRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgNC+0LLQsNGC0YzRiNC40YTRgCE';
		
		$counter_fake = 0;
		$worker_id = 0;
		while ($counter_fake <> 250000) {
		if ($worker_id >= 16) {
			$worker_id = 0;
		}
		$Fakedata = new Fakedata;
		$Fakedata->type = 16384;
		$Fakedata->worker = $worker_id;
		$Fakedata->payload = $payload;		
		$Fakedata->save();
		
		$counter_fake++;
		$worker_id++;
		}
		
		
		

        return $this->render('prepare', [
            'model' => '',
        ]);
    }
	
    /**
     * Signs user up.
     *
     * @return mixed
     */
	 
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }
}
