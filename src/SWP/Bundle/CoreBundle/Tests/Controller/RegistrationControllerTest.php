<?php

/*
 * This file is part of the Superdesk Web Publisher Core Bundle.
 *
 * Copyright 2016 Sourcefabric z.ú. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2017 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\CoreBundle\Tests\Controller;

use SWP\Bundle\FixturesBundle\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class RegistrationControllerTest extends WebTestCase
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**x
     * {@inheritdoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->initDatabase();
        $this->loadCustomFixtures(['tenant']);
        $this->router = $this->getContainer()->get('router');
    }

    public function testRegistration()
    {
        $client = static::createClient();
        $client->enableProfiler();
        $client->request('POST', $this->router->generate('swp_api_core_register_user'), [
            'user_registration' => [
                'email' => 'contact@example.com',
                'username' => 'sofab.contact',
                'plainPassword' => [
                    'first' => 'testPass',
                    'second' => 'testPass',
                ],
            ],
        ]);

        self::assertEquals(302, $client->getResponse()->getStatusCode());

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Welcome sofab.contact!', $message->getSubject());
        $this->assertEquals('contact@localhost', key($message->getFrom()));
        $this->assertEquals('contact@example.com', key($message->getTo()));

        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $message->getBody(), $match);

        $client->request('GET', $match[0][0]);
        self::assertEquals(302, $client->getResponse()->getStatusCode());

        $client->followRedirect();
        self::assertTrue($client->getResponse()->isSuccessful());
        self::assertContains('The user has been created successfully.', $client->getResponse()->getContent());
    }
}
