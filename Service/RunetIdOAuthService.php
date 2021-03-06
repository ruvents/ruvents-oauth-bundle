<?php

namespace Ruvents\OAuthBundle\Service;

use Ruvents\OAuthBundle\OAuthData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RunetIdOAuthService extends AbstractOAuthService
{
    const NAME = 'runet_id';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl($redirectUrl, $state = null)
    {
        return $this->uriFactory
            ->createUri('')
            ->withScheme('https')
            ->withHost('runet-id.com')
            ->withPath('/oauth/main/dialog')
            ->withQuery(http_build_query([
                'apikey' => $this->options['key'],
                'url' => $redirectUrl,
            ]))
            ->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        return $request->query->get('token');
    }

    /**
     * {@inheritdoc}
     */
    public function getData($token, $redirectUrl)
    {
        $rawData = $this->makeRequestAndJsonDecode('api.runet-id.com', '/user/auth', [
            'ApiKey' => $this->options['key'],
            'Hash' => md5($this->options['key'].$this->options['secret']),
            'token' => $token,
        ], [], [], 'GET', 'http');

        $data = new OAuthData();
        $data->id = isset($rawData['RunetId']) ? $rawData['RunetId'] : null;
        $data->email = isset($rawData['Email']) ? $rawData['Email'] : null;
        $data->firstName = isset($rawData['FirstName']) ? $rawData['FirstName'] : null;
        $data->lastName = isset($rawData['LastName']) ? $rawData['LastName'] : null;
        $data->middleName = isset($rawData['MiddleName']) ? $rawData['MiddleName'] : null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'key',
                'secret',
            ])
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('secret', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsState()
    {
        return false;
    }
}
