<p align="center"><img src="./src/icon.svg" width="100" height="100" alt="icon"></p>

<h1 align="center">ID.me Verification</h1>



## Requirements

This plugin requests Craft 4.x and Craft Commerce 4.x

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “ID.me”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project

# tell Composer to load the plugin
composer require webdna/idme

# tell Craft to install the plugin
./craft install/plugin idme
```

## Setup

Create an app on ID.me (https://developers.id.me/organizations), use the callback url specified in the plugin settings for your app redirect url.

Once the app is created and the groups selected, then enter the Client ID & Client Secret (environmental variable are recommended) in the plugin settings.

Also specify which groups (in the plugin settings) you want to be able to verify. These cannot be ones that have not be selected in your ID.me app. 


## Usage

Add the verify with ID.me button to your page (usually the cart page) by using the following code:

```twig
{{ craft.idme.renderVerifyButton(number, text, mode, redirectUrl)|raw }}
```

### Parameters

`number` : the cart number

`text` : the text displayed above the button

`mode` : 'popup' or 'fullpage' (default: 'popup')

`redirectUrl` : the url to the page after verification, usually the same as the button. Only used and required if `mode` is `fullpage`


## Basic Example

```twig
{{ craft.idme.renderVerifyButton(cart.number, 'Military members receive 10% off with ID.me')|raw }}
```
