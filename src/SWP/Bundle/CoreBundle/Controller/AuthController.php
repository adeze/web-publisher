<?php

/*
 * This file is part of the Superdesk Web Publisher Core Bundle.
 *
 * Copyright 2016 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2016 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\CoreBundle\Controller;

use GuzzleHttp;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SWP\Bundle\CoreBundle\Form\Type\SuperdeskCredentialAuthenticationType;
use SWP\Bundle\CoreBundle\Form\Type\UserAuthenticationType;
use SWP\Bundle\CoreBundle\Model\UserInterface;
use SWP\Component\Common\Response\ResponseContext;
use SWP\Component\Common\Response\SingleResourceResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class AuthController extends Controller
{
    /**
     * Look for user matching provided credentials.
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Look for user matching provided credentials",
     *     statusCodes={
     *         200="Returned on success.",
     *         401="No user found or not authorized."
     *     },
     *     input="SWP\Bundle\CoreBundle\Form\Type\UserAuthenticationType"
     * )
     * @Route("/api/{version}/auth/", options={"expose"=true}, defaults={"version"="v1"}, name="swp_api_auth")
     * @Method("POST")
     */
    public function authenticateAction(Request $request)
    {
        $form = $this->createForm(UserAuthenticationType::class, []);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $formData = $form->getData();
            try {
                $user = $this->get('swp.security.user_provider')->loadUserByUsername($formData['username']);
            } catch (UsernameNotFoundException $e) {
                $user = null;
            }

            if (null !== $user) {
                if ($this->get('security.password_encoder')->isPasswordValid($user, $formData['password'])) {
                    return $this->getApiToken($user, null);
                }
            }
        }

        return new SingleResourceResponse([
            'status' => 401,
            'message' => 'Unauthorized',
        ], new ResponseContext(401));
    }

    /**
     * Ask Superdesk server for user with those credentials and tries to authorize.
     *
     * @ApiDoc(
     *     resource=true,
     *     description="Authorize using Superdesk credentials",
     *     statusCodes={
     *         200="Returned on success.",
     *         401="No user found or not authorized."
     *     },
     *     input="SWP\Bundle\CoreBundle\Form\Type\SuperdeskCredentialAuthenticationType"
     * )
     * @Route("/api/{version}/auth/superdesk/", options={"expose"=true}, defaults={"version"="v1"}, name="swp_api_auth_superdesk")
     * @Method("POST")
     */
    public function authenticateWithSuperdeskAction(Request $request)
    {
        $form = $this->createForm(SuperdeskCredentialAuthenticationType::class, []);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $formData = $form->getData();
            $authorizedSuperdeskHosts = $this->container->getParameter('superdesk_servers');
            $superdeskUser = null;
            $client = new GuzzleHttp\Client();

            foreach ($authorizedSuperdeskHosts as $baseUrl) {
                try {
                    $apiRequest = new GuzzleHttp\Psr7\Request('GET', sprintf('%s/api/sessions/%s', $baseUrl, $formData['session_id']), [
                        'Authorization' => $formData['token'],
                    ]);
                    $apiResponse = $client->send($apiRequest);
                } catch (GuzzleHttp\Exception\ClientException $e) {
                    if ($e->getResponse()->getStatusCode() !== 200) {
                        continue;
                    }
                }

                if ($apiResponse->getStatusCode() !== 200) {
                    continue;
                }

                $content = json_decode($apiResponse->getBody()->getContents(), true);
                if (is_array($content) && array_key_exists('user', $content)) {
                    $superdeskUser = $content['user'];

                    break;
                }
            }

            if (null == $superdeskUser) {
                return new SingleResourceResponse([
                    'status' => 401,
                    'message' => 'Unauthorized (user not found in Superdesk)',
                ], new ResponseContext(401));
            }

            $userProvider = $this->get('swp.security.user_provider');
            $publisherUser = $userProvider->findOneByEmail($superdeskUser['email']);
            if (null === $publisherUser) {
                try {
                    $publisherUser = $userProvider->loadUserByUsername($superdeskUser['username']);
                } catch (UsernameNotFoundException $e) {
                    $publisherUser = null;
                }
            }

            if (null === $publisherUser) {
                $userManager = $this->get('fos_user.user_manager');
                /** @var UserInterface $publisherUser */
                $publisherUser = $userManager->createUser();
                $publisherUser->setUsername($superdeskUser['username']);
                $publisherUser->setEmail($superdeskUser['email']);
                $publisherUser->setRoles(['ROLE_INTERNAL_API', 'ROLE_USER']);
                $publisherUser->setFirstName($superdeskUser['first_name']);
                $publisherUser->setLastName($superdeskUser['last_name']);
                $publisherUser->setPlainPassword(password_hash(random_bytes(36), PASSWORD_BCRYPT));
                $publisherUser->setEnabled(true);
                $userManager->updateUser($publisherUser);
            }

            if (null !== $publisherUser) {
                return $this->getApiToken($publisherUser, str_replace('Basic ', '', $formData['token']));
            }
        }

        return new SingleResourceResponse([
            'status' => 401,
            'message' => 'Unauthorized',
        ], new ResponseContext(401));
    }

    private function getApiToken($user, $token)
    {
        $apiKey = null;
        $apiKeyRepository = $this->get('swp.repository.api_key');
        if (null !== $token) {
            $apiKey = $apiKeyRepository
                ->getValidToken($token)
                ->getQuery()
                ->getOneOrNullResult();
        } else {
            $apiKey = $apiKeyRepository->getValidTokenForUser($user)->getQuery()->getOneOrNullResult();
        }

        if (null === $apiKey) {
            $apiKey = $this->get('swp.factory.api_key')->create($user, $token);
            $apiKeyRepository->add($apiKey);
        }

        return new SingleResourceResponse([
            'token' => [
                'api_key' => $apiKey->getApiKey(),
                'valid_to' => $apiKey->getValidTo(),
            ],
            'user' => $user,
        ]);
    }
}
