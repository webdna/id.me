<?php
namespace webdna\idme\controllers;

use webdna\idme\Plugin;

use Craft;
use craft\web\Controller;
use craft\web\Response;

class DefaultController extends Controller
{
    protected array|bool|int $allowAnonymous = ['callback'];

    public function actionCallback(): Response
    {
        $code = $this->request->getQueryParam('code');
        $state = $this->request->getQueryParam('state',null);

        if (!$code) {
            return null;
        }

        Plugin::getInstance()->service->processVerification($code,$state);
        // where should we redirect to?
        return $this->redirect('/cart');
    }
}