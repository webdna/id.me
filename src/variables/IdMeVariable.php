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
        $html = '<div style="display:inline-flex; height:40px; gap:10px; padding:10px; color:white; background-color:#2E3F51; border-radius:5px;"><img src="https://s3.amazonaws.com/idme-design/brand-assets/Primary-IDme-Logo-RGB-white.png" alt="id.me" style="height:100%; "/> <span>Verified</span></div>';
        
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