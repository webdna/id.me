<?php

namespace webdna\idme\models;

use craft\base\Model;
use craft\helpers\App;
use craft\helpers\Json;

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
    public string $display = 'popup';

    public function rules(): array
    {
        return [
            [['clientId', 'clientSecret'], 'required'],
        ];
    }

    public function getClientId(): string
    {
        if (!empty($this->clientId)) {
            return App::parseEnv($this->clientId);
        }
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        if (!empty($this->clientSecret)) {
            return App::parseEnv($this->clientSecret);
        }
        return $this->clientSecret;
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

}
