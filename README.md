# Minecraft OAuth
`Forked from tadhgboyle.`
`Updated by TheGostsniperfr for PHP 8 and Laravel 11 support.`



Provides easy layer to get a Minecraft profile (UUID, username, skins, capes) from a Microsoft Live OAuth session.


## Usage

You have different solutions for creating the request, here's one that works.

In addition, you'll need to register your Azure app with Mojang for your app to be authorized to use the Mojang api, otherwise you'll get a **403** error code by Mojang API.

To do this, you need to fill in [this form]("https://aka.ms/mce-reviewappid") (response within a few weeks)

### Create MS request

```php
$clientId = 'AZURE_OAUTH_CLIENT_ID';
$redirectUri = urlencode('REDIRECT_URI');

$authUrl = "https://login.live.com/oauth20_authorize.srf?client_id=$clientId&response_type=code&redirect_uri=$redirectUri&scope=XboxLive.signin%20offline_access&state=NOT_NEEDED";
```

Nb: Apparently, it's possible to use `service::user.auth.xboxlive.com::MBI_SSL` as a scope to avoid confirming microsoft permissions.

Unfortunately, I haven't had any conclusive results on this, so if you have any information on this subject, I'd be delighted to have it.

### Receive MS responce

```php
require 'vendor/autoload.php';

$client_id = '<Azure OAuth Client ID>';
$client_secret = '<Azure OAuth Client Secret>';
$redirect_uri = '<URL to this file>';

try {
    $profile = (new \Arffornia\MinecraftOauth\MinecraftOauth)->fetchProfile(
        $client_id,
        $client_secret,
        $_GET['code'],
        $redirect_uri,
    );
} catch (\Arffornia\MinecraftOauth\Exceptions\MinecraftOauthException $e) {
    echo $e->getMessage();
}

echo 'Minecraft UUID: ' . $profile->uuid();
echo 'Minecraft Username: ' . $profile->username();
echo 'Minecraft Skin URL: ' . $profile->skins()[0]->url();
echo 'Minecraft Cape URL: ' . $profile->capes()[0]->url();
```


## Info

If you want to check all the [MS Auth Scheme]("https://wiki.vg/Microsoft_Authentication_Scheme").