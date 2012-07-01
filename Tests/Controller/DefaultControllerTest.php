<?php

namespace Br\ComponentsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{

    public function testAll() // deactivated
    {
        $client = static::createClient();
        
        /* Determine if the nocline main button is there */
        $crawler = $client->request('GET', '/');
        $this->assertCount(1, $crawler->filter('div#nocline-d-iframe'));
        $this->assertCount(1, $crawler->filter('div#nocline-d-w-o'));
        
        
        /* See if I can get the list of commands */
        $crawler = $client->request('GET', '/_nocline_/');
        // list is not empty
        $this->assertGreaterThan(0, $crawler->filter('ul#nocline-mw-ll a')->count());
        
        
        /* Get a command form that requires load javascript */
        $crawler  = $client->request('GET', '/_nocline_/form/Generate/bundle');
        $response = json_decode($client->getResponse()->getContent(), true);
        //is valid json
        $this->assertTrue(json_last_error() == JSON_ERROR_NONE);
        //I have the javascript tag
        $this->assertRegExp('#<script type=\"text\/javascript\">#', $response['html']);
        //I have the form tag
        $this->assertRegExp('#<form#', $response['html']);
        
        
        /* Get a command form that requires load a multiple input text */
        $crawler  = $client->request('GET', '/_nocline_/form/Doctrine/mapping:convert');
        $response = json_decode($client->getResponse()->getContent(), true);
        //I have the javascript tag
        $this->assertRegExp('#<span class=\"nocline-span-add\">#', $response['html']);
        
        
        /* successfully execute a command and see if I get the right successful output */
        // Get container:debug form
        $crawler = $client->request('GET', '/_nocline_/form/Container/debug');
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $post = array
        (
            'command' => array
            (
                '_token'           =>	$this->getFormCsrfToken($response['html']),
                'commandNamespace' => 'Container',
                'commandTitle'     => 'debug',
                'name'             => 'session',
            )
        );
        
        // Send post request to execute command
        $crawler = $client->request('POST', '/_nocline_/exec', $post);
        $this->assertRegExp('#Information for service session#', $client->getResponse()->getContent());
        
        // unsuccessfully execute a command and see if I get the right unsuccessful output
        
    }
    
    protected function getFormCsrfToken($html)
    {
        $partA = explode('name="command[_token]"', $html);
        $partB = explode('"', $partA[1]);
        
        return $partB[1];
    }
}
