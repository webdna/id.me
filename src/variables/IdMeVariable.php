<?php

namespace webdna\idme\variables;

use webdna\idme\Plugin;

use Craft;
use craft\helpers\Html;
use craft\helpers\UrlHelper;

class IdMeVariable
{
    public function renderVerifyButton(?string $state=null, ?string $text=null): string
    {
        $settings = Plugin::getInstance()->settings;
        Craft::$app->getView()->registerJsFile('https://s3.amazonaws.com/idme/developer/idme-buttons/assets/js/idme-wallet-button.js', ['defer' => true], null);
        $html = Html::tag(
            'span',
            '',
            [                
                'id' => 'idme-wallet-button',
                'data' => [
                    'client-id' => $settings->getClientId(),
                    'redirect' => UrlHelper::siteUrl($settings::REDIRECT_URL),
                    'response' => 'code',
                    'scope' => implode(',',$settings->groups),
                    'text' => $text,
                    'logo' => null,
                    'hero' => null,
                    'state' => $state,
                    'display' => $settings->display,
                    'show-verify' => false
                ]
            ]
        );
        
        return $html;
    }
}