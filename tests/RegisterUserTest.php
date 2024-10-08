<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterUserTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $client->request('GET', '/inscription');

        $client->submitForm('Valider', [
            'register_user[email]' => 'tyty@tyty.fr',
            'register_user[plainPassword][first]' => 'tyty',
            'register_user[plainPassword][second]' => 'tyty',
            'register_user[firstname]' => 'tyty',
            'register_user[lastname]' => 'tyty',
        ]);

        $this->assertResponseRedirects('/connexion');
        $client->followRedirect();

        $this->assertSelectorExists('div:contains("Votre compte a bien été crée. Veuillez vous connecter")');
    }
}
