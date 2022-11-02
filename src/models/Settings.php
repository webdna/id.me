<?php

namespace webdna\idme\models;

use craft\base\Model;
use craft\helpers\App;
use craft\helpers\Json;
use craft\helpers\UrlHelper;

class Settings extends Model
{
    public const ALL_GROUPS = [
        'alumni' => 'Alumni',
        'employee' => 'Company Employee' ,
        'responder' => 'First Responder' ,
        'government' => 'Government Employee' ,          
        'military' => 'Military' ,
        'nurse' => 'Nurse' ,
        'student' => 'Student' ,
        'teacher' => 'Teacher'
    ];

    public const REDIRECT_URL = 'actions/idme/default/callback';

    public string $clientId = '';
    public string $clientSecret = '';
    public array $groups = [];
    public ?int $logoId = null;
    public ?int $heroId = null;
    

    public function rules(): array
    {
        return [
            [['clientId', 'clientSecret'], 'required'],
        ];
    }

    public function getClientId(bool $parse = true): string
    {
        return $parse ? App::parseEnv($this->clientId) : $this->clientId;
    }

    public function getClientSecret(bool $parse = true): string
    {
        return $parse ? App::parseEnv($this->clientSecret) : $this->clientSecret;
    }

    public function getGroupOptions(): array
    {
        $options = [];
        foreach ($this::ALL_GROUPS as $value => $label) {
            array_push($options,[
                'value' => $value,
                'label' => $label
            ]);
        }
        return $options;
    }

    public function getRedirectUrl(): string
    {
        return UrlHelper::siteUrl($this::REDIRECT_URL);
    }
}
