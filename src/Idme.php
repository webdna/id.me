<?php

namespace webdna\idme;

use webdna\idme\behaviors\VerifiedGroupsBehavior;
use webdna\idme\models\Settings;
use webdna\idme\services\Service;
use webdna\idme\variables\IdMeVariable;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\events\DefineBehaviorsEvent;
use craft\web\twig\variables\CraftVariable;

use craft\commerce\events\DiscountEvent;
use craft\commerce\services\Discounts;

use craft\commerce\adjusters\Discount;
use craft\commerce\models\Discount as DiscountModel;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\models\LineItem;
use craft\commerce\events\DiscountAdjustmentsEvent;
use craft\commerce\events\MatchLineItemEvent;

use yii\base\Event;

class Idme extends Plugin
{
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();

        $this->setComponents([
            'service' => Service::class
        ]);

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Craft::$app->getView()->hook('cp.commerce.discounts.edit', [$this->service, 'discountTabsOrder']);
            Craft::$app->getView()->hook('cp.commerce.discounts.edit.content', [$this->service, 'addIdMeTab']);
        }

        Event::on(
            Discounts::class,
            Discounts::EVENT_AFTER_SAVE_DISCOUNT,
            function (DiscountEvent $event) {
                $discountId = $event->discount->id;
                $groups = Craft::$app->getRequest()->getBodyParam('groups');
                $this->service->saveIdMeDiscount($discountId, $groups);
            }
        );
        
        Event::on(
            Discount::class,
            Discount::EVENT_AFTER_DISCOUNT_ADJUSTMENTS_CREATED,
            function(DiscountAdjustmentsEvent $event) {
                $discount = $event->discount;
                $selectedGroups = $this->service->getGroupsByDiscountId($discount->id);
                if ($selectedGroups) {
                    $event->isValid = false;
                    
                    if ($idme = Craft::$app->getSession()->get('id.me')) {
                        foreach ($idme['status'] as $group) {
                            if ($group['verified'] && in_array($group['group'], $selectedGroups)) {
                                $event->isValid = true;
                            }
                        }
                    }
                }
            }
        );
        
        Event::on(
            Discounts::class,
            Discounts::EVENT_DISCOUNT_MATCHES_LINE_ITEM,
            function (MatchLineItemEvent $event) {
                $discount = $event->discount;
                $selectedGroups = $this->service->getGroupsByDiscountId($discount->id);
                if ($selectedGroups) {
                    $event->isValid = false;
                    
                    if ($idme = Craft::$app->getSession()->get('id.me')) {
                        foreach ($idme['status'] as $group) {
                            if ($group['verified'] && in_array($group['group'], $selectedGroups)) {
                                $event->isValid = true;
                            }
                        }
                    }
                }
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            static function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('idme', IdMeVariable::class);
            }
        );

    }

    protected function createSettingsModel(): Model
    {
        return new Settings();
    }

    protected function settingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'idme/settings',
            [ 'settings' => $this->getSettings() ]
        );
    }
}
