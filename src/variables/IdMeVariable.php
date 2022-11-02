<?php

namespace webdna\idme\variables;

use webdna\idme\Idme;

use Craft;
use craft\helpers\Html;
use craft\helpers\UrlHelper;

class IdMeVariable
{
    public function renderVerifyButton(?string $state=null, ?string $text=null, string $mode='popup', ?string $redirect=null): string
    {
        $html = '';
        
        //Craft::$app->getSession()->set('id.me', null);
        if (!Craft::$app->getSession()->get('id.me')) {
        
            $settings = Idme::getInstance()->settings;
                
            if ($settings->getClientId() && $settings->getClientSecret()) {
                
                Craft::$app->getView()->registerJsFile('https://s3.amazonaws.com/idme/developer/idme-buttons/assets/js/idme-wallet-button.js', ['defer' => true], null);
                $html = Html::tag(
                    'span',
                    '',
                    [                
                        'id' => 'idme-wallet-button',
                        'data' => [
                            'client-id' => $settings->getClientId(),
                            'redirect' => $settings->getRedirectUrl(),
                            'response' => 'code',
                            'scope' => implode(',',$settings->groups),
                            'text' => $text,
                            'logo' => null,
                            'hero' => null,
                            'state' => $mode == 'fullpage' && $redirect ? implode('||', [$state, $redirect]) : $state,
                            'display' => $mode,
                            'show-verify' => 'true'
                        ]
                    ]
                );
                
            }
            
        }
        
        return $html;
    }
}