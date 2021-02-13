<?php

namespace app\controllers;

use Yii;

use app\models\Products;
use app\models\Packages;
use app\models\Eds;
use app\models\Cities;
use app\models\Regions;
use app\models\Clients;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use app\models\SignupForm;
use app\models\Addresses;
use yii\helpers\ArrayHelper;
use app\models\UploadForm;
use yii\web\UploadedFile;



class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
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
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {

        $user = new User();

        $model = new SignupForm();
        if($model->load(Yii::$app->request->post()) && $model->validate()   ){
            $user->password = $model->password;
            $user->username = $model->username;
            $user->role_id = '2';

            if($user->save()){
                $this->redirect(['/site/login']);
            }
        }

        return $this->render('signup', compact('model'));
    }
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionClients()
    {
        $clients = Clients::find()->all();

        return $this->render('clients', compact('clients'));
    }

    public function actionRemoveclients($id)
    {
        if (Yii::$app->user->identity->role_id == 1) {


            $oneclient = Clients::findOne($id);
            $oneclient->delete();

            return $this->redirect('clients');
        } else {
            return $this->goHome();
        }
    }

    public function actionCities($idedit = null)
    {

        if (Yii::$app->user->identity->role_id == 1) {


            $buttonname = 'Добавить';
            $cities = Cities::find()->all();

            if ($idedit) {
                $buttonname = 'Редактировать';
                $model = Cities::findOne($idedit);
                if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                    return $this->redirect('cities');
                }
                return $this->render('cities', compact('cities', 'model', 'buttonname'));
            }
            $model = new Cities();

            if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                return $this->redirect('cities');
            }

            return $this->render('cities', compact('cities', 'model', 'buttonname'));
        } else {
            return $this->goHome();
        }
    }

    public function actionUsers()
    {
        $users = User::find()->all();

        return $this->render('users', compact('users'));
    }


    public function actionRemovecities($id)
    {
        if (Yii::$app->user->identity->role_id == 1) {


            $onecity = Cities::findOne($id);
            $onecity->delete();

            return $this->redirect('cities');
        } else {
            return $this->goHome();
        }
    }



    public function actionProducts($idedit = null)
    {

        if (Yii::$app->user->identity->role_id == 1 || Yii::$app->user->identity->role_id == 2) {


            $buttonname = 'Добавить';
            $products = Products::find()->all();
            $cities = Cities::find()->all();
            $items_city = ArrayHelper::map($cities,'id','name');
            $eds = Eds::find()->all();
            $items_ed = ArrayHelper::map($eds,'id','type');;


            if ($idedit) {
                $buttonname = 'Редактировать';
                $model = Products::findOne($idedit);
                if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                    return $this->redirect('products');
                }
                return $this->render('products', compact('products', 'model', 'buttonname', 'items_city', 'items_ed'));
            }
            $model = new Products();

            if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                return $this->redirect('products');
            }

            return $this->render('products', compact('products', 'model', 'buttonname', 'items_city', 'items_ed'));
        } else {
            return $this->goHome();
        }
    }

    public function actionPackages($product_id , $idedit = null)
    {

        if (Yii::$app->user->identity->role_id == 1 || Yii::$app->user->identity->role_id == 2) {


            $buttonname = 'Добавить';
            $packages = Packages::find()->where('product_id='.$product_id)->all();

            if ($idedit) {
                $buttonname = 'Редактировать';
                $model = Packages::findOne($idedit);
                if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                    return $this->redirect('packages?product_id='.$product_id);
                }
                return $this->render('packages', compact('packages', 'model', 'buttonname', 'product_id'));
            }
            $model = new Packages();

            if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                return $this->redirect('packages?product_id='.$product_id);
            }

            return $this->render('packages', compact('packages', 'model', 'buttonname', 'product_id'));
        } else {
            return $this->goHome();
        }
    }

    public function actionAddresses($package_id , $idedit = null)
    {

        if (Yii::$app->user->identity->role_id == 1 || Yii::$app->user->identity->role_id == 2) {


            $buttonname = 'Добавить';
            $addresses = Addresses::find()->where('package_id=' . $package_id)->all();
            $regions = Regions::find()->all();
            $items_region = ArrayHelper::map($regions,'id','name');

            if ($idedit) {
                $buttonname = 'Редактировать';
                $model = Addresses::findOne($idedit);
                if ($model->load(Yii::$app->request->post()) && $model->save()) {
                    return $this->redirect('packages?product_id=' . $product_id);
                }
                return $this->render('addresses', compact('addresses', 'model', 'buttonname', 'package_id', 'items_region'));
            }
            $model = new Addresses();

            if ($model->load(Yii::$app->request->post())) {
                $model->img = UploadedFile::getInstance($model, 'img');
                if ($model->img !== null) {
                    $model->img->saveAs('uploads/' . $model->img->basename . '.' . $model->img->extension);
                    $model->img = 'uploads/' . $model->img->basename . '.' . $model->img->extension;
                    $model->save();
                    return $this->redirect('addresses?package_id='.$package_id);
                } else {
                    $model->img = 'none';
                    return $this->redirect('addresses?package_id='.$package_id);
                }
            }


            return $this->render('addresses', compact('addresses', 'model', 'buttonname', 'package_id', 'items_region'));
        } else {
            return $this->goHome();
        }
    }

    public function actionRemovepackage($product_id, $idremove)
    {
        if (Yii::$app->user->identity->role_id == 1) {


            $onepackage = Packages::findOne($idremove);
            /*$addresses
            for ($i = 0; $i < count($addresses); $i++) {
                $addresses[$i]->delete();
            }*/
            $onepackage->delete();

            return $this->redirect('packages?product_id='.$product_id);
        } else {
            return $this->goHome();
        }
    }


    public function actionRegions($city_id , $idedit = null)
    {

        if (Yii::$app->user->identity->role_id == 1) {


            $buttonname = 'Добавить';
            $regions = Regions::find()->where('city_id='.$city_id)->all();

            if ($idedit) {
                $buttonname = 'Редактировать';
                $model = Regions::findOne($idedit);
                if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                    return $this->redirect('regions?city_id='.$city_id);
                }
                return $this->render('regions', compact('regions', 'model', 'buttonname', 'city_id'));
            }
            $model = new Regions();

            if ($model->load(Yii::$app->request->post()) && $model->save() && Yii::$app->user->identity->role_id == 1) {
                return $this->redirect('regions?city_id='.$city_id);
            }

            return $this->render('regions', compact('regions', 'model', 'buttonname', 'city_id'));
        } else {
            return $this->goHome();
        }
    }


    public function actionRemoveregion($city_id, $idremove)
    {
        if (Yii::$app->user->identity->role_id == 1) {


            $oneregion = Regions::findOne($idremove);
            $oneregion->delete();

            return $this->redirect('regions?city_id='.$city_id);
        } else {
            return $this->goHome();
        }
    }



    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
