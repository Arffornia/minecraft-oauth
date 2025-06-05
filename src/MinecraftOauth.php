<?php

namespace Arffornia\MinecraftOauth;

use Arffornia\MinecraftOauth\DataRetrievers\AccessTokenRetriever;
use Arffornia\MinecraftOauth\Exceptions\GameOwnershipCheckException;
use Arffornia\MinecraftOauth\Exceptions\ResponseValidationException;
use Arffornia\MinecraftOauth\Exceptions\XtxsTokenRetrievalException;
use Arffornia\MinecraftOauth\DataRetrievers\GameOwnershipStatusRetriever;
use Arffornia\MinecraftOauth\DataRetrievers\MinecraftAccessTokenRetriever;
use Arffornia\MinecraftOauth\DataRetrievers\MinecraftProfileDataRetriever;
use Arffornia\MinecraftOauth\DataRetrievers\XblTokenUserHashRetriever;
use Arffornia\MinecraftOauth\DataRetrievers\XtxsTokenRetriever;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MinecraftOauth
{
    private Client $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?? new Client([
            'http_errors' => false,
        ]);
    }

    /**
     * Fetch profile using MS OAuth UI flow.
     *
     * @throws ResponseValidationException
     * @throws GameOwnershipCheckException
     * @throws GuzzleException
     * @throws XtxsTokenRetrievalException
     */
    public function fetchProfileWithOAuthUI(
        string $clientId,
        string $clientSecret,
        string $code,
        string $redirectUri
    ): MinecraftProfile {
        $accessToken = (new AccessTokenRetriever($this->client))
            ->retrieve(
                $clientId,
                $clientSecret,
                $code,
                urlencode($redirectUri)
            );

        return $this->fetchProfileWithAccessToken($accessToken);
    }

    /**
     * Fetch profile using MS access token.
     *
     * @throws ResponseValidationException
     * @throws GameOwnershipCheckException
     * @throws GuzzleException
     * @throws XtxsTokenRetrievalException
     */
    public function fetchProfileWithAccessToken(string $accessToken): MinecraftProfile
    {
        [$xblToken, $userHash] = (new XblTokenUserHashRetriever($this->client))
            ->retrieve($accessToken);


        $xtxsToken = (new XtxsTokenRetriever($this->client))
            ->retrieve($xblToken);

        $minecraftAccessToken = (new MinecraftAccessTokenRetriever($this->client))
            ->retrieve($userHash, $xtxsToken);

        (new GameOwnershipStatusRetriever($this->client))
            ->check($minecraftAccessToken);

        $minecraftProfileData = (new MinecraftProfileDataRetriever($this->client))
            ->retrieve($minecraftAccessToken);

        return new MinecraftProfile($minecraftProfileData);
    }
}
