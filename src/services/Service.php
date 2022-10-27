<?php

namespace webdna\idme\services;

use webdna\idme\Plugin;
use webdna\idme\records\IdMeDiscount;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\UrlHelper;

use craft\commerce\Plugin as Commerce;
use craft\commerce\db\Table as CommerceTable;
use craft\commerce\models\Discount;

use League\OAuth2\Client\Provider\GenericProvider;

/**
 * @author    Web DNA
 * @package   MultisiteVariants
 * @since     1.0.0
 */
class Service extends Component
{
    public function getCouponCodesByGroup(string $group): array
    {
        $discountIds = (new Query())
            ->select('discountId')
            ->from('{{%idme_discounts}}')
            ->where(['like','groups',$group])
            ->all();
        
        $codes = [];

        foreach (array_column($discountIds, 'discountId') as $discountId) {
            foreach (Commerce::getInstance()->getCoupons()->getCouponsByDiscountId($discountId) as $coupon) {
                array_push($codes, $coupon->code);
            }
        }
        return $codes;
    }

    public function getGroupsByDiscountId(int $discountId): ?array
    {
        if (!$discountId) {
            return null;
        }

        $discountRecord = IdMeDiscount::findOne(['discountId' => $discountId]);
        if (!$discountRecord) {
            return null;
        }

        $groups = Json::decodeIfJson($discountRecord->groups);
        
        if (!is_array($groups) || empty($groups)) {
            return null;
        }

        return $groups;
    }

    public function saveIdMeDiscount($discountId, $groups): bool
    {
        $discountRecord = IdMeDiscount::findOne(['discountId' => $discountId]);

        if ((!$groups || empty($groups))) {
            if ($discountRecord) {
                $discountRecord->delete();
            }
            return true;
        }

        if (!$discountRecord && $groups) {
            $discountRecord = new IdMeDiscount();
            $discountRecord->discountId = $discountId;
        }

        $discountRecord->groups = Json::encode($groups);

        return $discountRecord->save();
    }

    public function discountTabsOrder(array &$context): void
    {
        $tabs = $context['tabs'];
        $newTab = [
            'label' => 'Verified Groups',
            'url' => '#idme',
            'class' => '',
        ];
        array_splice($tabs,3,0,[$newTab]);
        $context['tabs'] = $tabs;
        
    }

    public function addIdMeTab(array &$context): string
    {
        $discountId = $context['discount']->id;
        $settings = Plugin::getInstance()->settings;
        $availableGroups = [];
        $selectedGroups = $this->getGroupsByDiscountId($discountId);

        foreach ($settings->getGroupOptions() as $option) {
            if (in_array($option['value'],$settings->groups)) {
                array_push($availableGroups, $option);
            }
        }

        return Craft::$app->view->renderTemplate(
            'idme/discounts-tab', [
                'availableGroups' => $availableGroups,
                'selectedGroups' => $selectedGroups
            ]
        );
    }

    public function processVerification($code, $state): bool
    {
        $provider = $this->_createProvider();
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $code, 
                'scope' => implode(',',Plugin::getInstance()->settings->groups)
            ]);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // LOG IT
            Craft::dd('authorization failed' . $e->getMessage());
            return false;
        }

        try {
            $resourceOwner = $provider->getResourceOwner($accessToken);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // LOG IT
            Craft::dd('couldn\'t get resource owner' . $e->getMessage());

            return false;
        }

        $this->applyGroupsDiscount($resourceOwner->toArray(),$state);

        return true;
    }

    public function applyGroupsDiscount(array $resourceOwner = [], string $state = ''): void
    {
        if (empty($resourceOwner) || !array_key_exists('status',$resourceOwner) || !$state) {
            return;
        }

        Commerce::getInstance()->getCarts()->setSessionCartNumber($state);
        $cart = Commerce::getInstance()->getCarts()->getCart();

        $couponCode = '';
        foreach ($resourceOwner['status'] as $group) {
            if ($group['verified'] === true) {
                $codes = $this->getCouponCodesByGroup($group['group']);
                if (!empty($codes)) {
                    // just do the first one?
                    $couponCode = $codes[0];
                    break;
                }
            }
        }
        $cart->couponCode = trim($couponCode);
        if (!Craft::$app->getElements()->saveElement($cart)) {
            // LOG IT
        }

        return;
    }

    private function _createProvider(): GenericProvider
    {
        $settings = Plugin::getInstance()->settings;
        $provider = new GenericProvider([
            'clientId'                => $settings->getClientId(),
            'clientSecret'            => $settings->getClientSecret(),
            'redirectUri'             => UrlHelper::siteUrl($settings::REDIRECT_URL),
            'urlAuthorize'            => 'https://api.id.me/oauth/authorize',
            'urlAccessToken'          => 'https://api.id.me/oauth/token',
            'urlResourceOwnerDetails' => 'https://api.id.me/api/public/v3/attributes.json',
        ]);

        return $provider;
    }

}