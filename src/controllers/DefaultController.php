<?php
namespace webdna\idme\controllers;

use webdna\idme\Idme;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use craft\web\View;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class DefaultController extends Controller
{
    protected array|bool|int $allowAnonymous = ['callback'];

    public function actionCallback(): ?Response
    {
        $code = $this->request->getQueryParam('code');
        $state = $this->request->getQueryParam('state', null);

        if (!$code) {
            throw new BadRequestHttpException(Craft::t('idme', $this->request->getQueryParam('error_description')));
        }
        
        if ($state) {
            $state = explode('||', urlDecode($state));
        }
        
        //Craft::dd($state);

        Idme::getInstance()->service->processVerification($code, $state[0]);
            
        $settings = Idme::getInstance()->settings;
            
        if (isset($state[1])) {
            
            return $this->redirect($state[1] ?? '');
            
        } else {
                
            $view = Craft::$app->getView();
                
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);
                
            $this->setView($view);
                
            return $this->renderTemplate('idme/modal');
            
        }
    }
}